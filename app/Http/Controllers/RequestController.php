<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Item;
use App\Models\ItemRequest;
use App\Models\Notification;
use App\Models\Rental;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * طلبات الباحث: إرسال طلب، إلغاؤه، طلباتي، الطلبات الواردة، وصفحة تفاصيل العملية
 */
class RequestController extends Controller
{
    /**
     * طلباتي (الطلبات اللي أرسلها المستخدم)
     */
    public function index(Request $request): View
    {
        Rental::autoConfirmDue();

        $all = ItemRequest::where('requester_id', $request->user()->id)
            ->with(['item.images', 'item.owner', 'rental', 'exchangeOffer.offeredItem', 'appointment'])
            ->latest('request_date')
            ->get();

        return view('requests.index', $this->withTabs($request, $all));
    }

    /**
     * الطلبات الواردة على إعلانات المستخدم
     */
    public function incoming(Request $request): View
    {
        Rental::autoConfirmDue();

        $all = ItemRequest::whereHas('item', fn ($q) => $q->where('owner_id', $request->user()->id))
            ->with(['item.images', 'requester', 'rental', 'exchangeOffer.offeredItem', 'appointment'])
            ->latest('request_date')
            ->get();

        return view('requests.incoming', $this->withTabs($request, $all));
    }

    /**
     * تفاصيل العملية (للطرفين)
     */
    public function show(Request $request, ItemRequest $itemRequest): View
    {
        Rental::autoConfirmDue();

        $itemRequest->load(['item.images', 'item.owner', 'item.category', 'requester', 'rental', 'exchangeOffer.offeredItem', 'appointments.proposer']);
        $user = $request->user();

        abort_unless($itemRequest->isParty($user) || $user->is_admin, 403);

        return view('requests.show', [
            'deal' => $itemRequest,
            'user' => $user,
            'isOwner' => $user->id === $itemRequest->ownerId(),
            'other' => $itemRequest->isParty($user) ? $itemRequest->otherParty($user) : $itemRequest->requester,
            'appointment' => $itemRequest->appointments->sortByDesc('id')->first(),
        ]);
    }

    /**
     * إرسال طلب على غرض
     */
    public function store(Request $request, Item $item): RedirectResponse
    {
        $user = $request->user();

        if ($item->owner_id === $user->id) {
            return back()->with(['toast' => 'لا يمكنك طلب غرضك', 'toast_type' => 'error']);
        }

        if ($item->state !== 'active' || $item->owner->is_blocked) {
            return back()->with(['toast' => 'هذا الغرض لم يعد متاحاً', 'toast_type' => 'error']);
        }

        // طلب واحد مفتوح لكل غرض
        $hasOpen = ItemRequest::where('item_id', $item->id)
            ->where('requester_id', $user->id)
            ->whereIn('status', ItemRequest::OPEN_STATUSES)
            ->exists();

        if ($hasOpen) {
            return back()->with(['toast' => 'لديك طلب مفتوح على هذا الغرض مسبقاً', 'toast_type' => 'error']);
        }

        $data = $this->validateRequest($request, $item);

        DB::transaction(function () use ($data, $item, $user) {
            $itemRequest = ItemRequest::create([
                'item_id' => $item->id,
                'requester_id' => $user->id,
                'requester_note' => $data['requester_note'] ?? null,
                'count' => $data['count'] ?? 1,
                'status' => 'waiting',
            ]);

            // التأجير: مدة التأجير المطلوبة
            if ($item->transaction_type === 'rent') {
                $itemRequest->rental()->create([
                    'start_date' => $data['start_date'],
                    'expected_return_date' => $data['end_date'],
                    'return_status' => 'pending',
                ]);
            }

            // التبادل: المقابل (غرض من أغراضي أو وصف)
            if ($item->transaction_type === 'exchange') {
                $itemRequest->exchangeOffer()->create([
                    'offered_item_id' => $data['offered_item_id'] ?? null,
                    'offered_description' => $data['offered_description'] ?? null,
                ]);
            }

            ActivityLog::record('create', $itemRequest, null, ['item_id' => $item->id, 'status' => 'waiting']);
            Notification::send($item->owner_id, 'طلب جديد', $user->full_name.' أرسل طلباً على "'.$item->title.'".');
        });

        return redirect()->route('requests.index')->with('toast', 'تم إرسال طلبك، سيصلك إشعار عند رد المالك');
    }

    /**
     * إلغاء الطلب (قبل قبول المالك فقط)
     */
    public function cancel(Request $request, ItemRequest $itemRequest): RedirectResponse
    {
        abort_unless($itemRequest->requester_id === $request->user()->id, 403);

        if ($itemRequest->status !== 'waiting') {
            return back()->with(['toast' => 'لا يمكن إلغاء الطلب بعد رد المالك', 'toast_type' => 'error']);
        }

        $itemRequest->update(['status' => 'canceled']);

        ActivityLog::record('update', $itemRequest, ['status' => 'waiting'], ['status' => 'canceled']);
        Notification::send($itemRequest->ownerId(), 'تم إلغاء طلب', $request->user()->full_name.' ألغى طلبه على "'.$itemRequest->item->title.'".');

        return back()->with('toast', 'تم إلغاء الطلب');
    }

    // ===== دوال مساعدة =====

    // التحقق من نموذج الطلب حسب نوع الغرض
    private function validateRequest(Request $request, Item $item): array
    {
        $rules = [
            'requester_note' => ['nullable', 'string', 'max:250'],
            'count' => ['nullable', 'integer', 'min:1', 'max:'.$item->count],
        ];
        $messages = [
            'requester_note.*' => 'الملاحظة 250 حرفاً كحد أقصى',
            'count.*' => 'الكمية المطلوبة غير متاحة',
        ];

        if ($item->transaction_type === 'rent') {
            $rules['start_date'] = ['required', 'date', 'after_or_equal:today'];
            $rules['end_date'] = ['required', 'date', 'after_or_equal:start_date'];
            $messages['start_date.*'] = 'اختر تاريخ بداية من اليوم فصاعداً';
            $messages['end_date.*'] = 'تاريخ الإرجاع يجب أن يكون بعد تاريخ البداية';
        }

        if ($item->transaction_type === 'exchange') {
            $rules['offered_item_id'] = [
                'nullable',
                'required_without:offered_description',
                Rule::exists('items', 'id')->where('owner_id', $request->user()->id)->where('state', 'active'),
            ];
            $rules['offered_description'] = ['nullable', 'required_without:offered_item_id', 'string', 'max:500'];
            $messages['offered_item_id.required_without'] = 'اختر غرضاً من أغراضك أو اكتب ما تعرضه مقابله';
            $messages['offered_item_id.exists'] = 'الغرض المختار غير متاح';
            $messages['offered_description.required_without'] = 'اختر غرضاً من أغراضك أو اكتب ما تعرضه مقابله';
            $messages['offered_description.*'] = 'الوصف 500 حرف كحد أقصى';
        }

        $data = $request->validate($rules, $messages);

        // مدة التأجير ما بتتجاوز الحد اللي حدده المالك
        if ($item->transaction_type === 'rent') {
            $days = Carbon::parse($data['start_date'])->diffInDays(Carbon::parse($data['end_date'])) + 1;
            $maxDays = $item->max_rent_duration * ['day' => 1, 'month' => 30, 'year' => 365][$item->rent_unit ?? 'day'];

            if ($item->max_rent_duration && $days > $maxDays) {
                throw ValidationException::withMessages([
                    'end_date' => 'أقصى مدة للتأجير '.$item->max_rent_duration.' '.$item->rentUnitLabel(),
                ]);
            }
        }

        return $data;
    }

    // فلترة حسب تبويب الحالة مع الأعداد
    private function withTabs(Request $request, $all): array
    {
        $counts = ['all' => $all->count()];
        foreach (['waiting', 'approved', 'received', 'rejected', 'canceled', 'finished'] as $status) {
            $counts[$status] = $all->where('status', $status)->count();
        }

        $tab = array_key_exists($request->input('tab'), $counts) ? $request->input('tab') : 'all';

        return [
            'requests' => $tab === 'all' ? $all : $all->where('status', $tab),
            'counts' => $counts,
            'tab' => $tab,
        ];
    }
}
