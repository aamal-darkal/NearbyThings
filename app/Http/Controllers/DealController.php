<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Appointment;
use App\Models\ItemRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * مراحل العملية بعد الطلب:
 * قبول / رفض (المالك) ← موعد الاستلام (تأكيد أو اقتراح موعد جديد) ← تأكيد الاستلام (الباحث)
 * ← بالتأجير: إشعار الإرجاع (الباحث) ← تأكيد الإرجاع أو الاعتراض (المالك، خلال 48 ساعة)
 */
class DealController extends Controller
{
    /**
     * قبول الطلب مع ملاحظة وموعد ومكان الاستلام
     */
    public function accept(Request $request, ItemRequest $itemRequest): RedirectResponse
    {
        $this->authorizeOwner($request, $itemRequest);

        if ($itemRequest->status !== 'waiting') {
            return back()->with(['toast' => 'تم الرد على هذا الطلب مسبقاً', 'toast_type' => 'error']);
        }

        $data = $request->validate([
            'owner_note' => ['nullable', 'string', 'max:250'],
            'meeting_date' => ['required', 'date', 'after:now'],
            'meeting_location' => ['required', 'string', 'max:150'],
        ], [
            'owner_note.*' => 'الملاحظة 250 حرفاً كحد أقصى',
            'meeting_date.*' => 'حدد موعد استلام في المستقبل',
            'meeting_location.*' => 'حدد مكان الاستلام',
        ]);

        DB::transaction(function () use ($itemRequest, $data, $request) {
            $itemRequest->update(['status' => 'approved', 'owner_note' => $data['owner_note'] ?? null]);

            $itemRequest->appointments()->create([
                'proposed_by' => $request->user()->id,
                'meeting_date' => $data['meeting_date'],
                'meeting_location' => $data['meeting_location'],
                'status' => 'new',
            ]);
        });

        ActivityLog::record('update', $itemRequest, ['status' => 'waiting'], ['status' => 'approved']);
        Notification::send($itemRequest->requester_id, 'تمت الموافقة على طلبك', 'وافق المالك على طلبك على "'.$itemRequest->item->title.'". راجع موعد الاستلام وأكده.', route('requests.show', $itemRequest));

        return back()->with('toast', 'تم قبول الطلب وإرسال الموعد');
    }

    /**
     * رفض الطلب مع ملاحظة
     */
    public function reject(Request $request, ItemRequest $itemRequest): RedirectResponse
    {
        $this->authorizeOwner($request, $itemRequest);

        if ($itemRequest->status !== 'waiting') {
            return back()->with(['toast' => 'تم الرد على هذا الطلب مسبقاً', 'toast_type' => 'error']);
        }

        $data = $request->validate([
            'owner_note' => ['required', 'string', 'min:3', 'max:250'],
        ], [
            'owner_note.*' => 'اكتب ملاحظة توضح سبب الرفض',
        ]);

        $itemRequest->update(['status' => 'rejected', 'owner_note' => $data['owner_note']]);

        ActivityLog::record('update', $itemRequest, ['status' => 'waiting'], ['status' => 'rejected']);
        Notification::send($itemRequest->requester_id, 'تم رفض طلبك', 'رفض المالك طلبك على "'.$itemRequest->item->title.'". ملاحظة المالك: '.$data['owner_note'], route('requests.show', $itemRequest));

        return back()->with('toast', 'تم رفض الطلب');
    }

    /**
     * تأكيد موعد الاستلام (من الطرف اللي ما اقترحه)
     */
    public function confirmAppointment(Request $request, ItemRequest $itemRequest): RedirectResponse
    {
        $user = $request->user();
        $appointment = $this->currentAppointment($request, $itemRequest);

        if (! $appointment || ! $appointment->awaitsConfirmationFrom($user)) {
            return back()->with(['toast' => 'لا يوجد موعد بانتظار تأكيدك', 'toast_type' => 'error']);
        }

        $appointment->update(['status' => 'confirmed']);

        $other = $itemRequest->otherParty($user);
        Notification::send($other->id, 'تم تأكيد موعد الاستلام', $user->full_name.' أكد موعد الاستلام لـ "'.$itemRequest->item->title.'".', route('requests.show', $itemRequest));

        return back()->with('toast', 'تم تأكيد الموعد');
    }

    /**
     * اقتراح موعد جديد (الموعد الحالي بينلغى، والطرف التاني بيأكد الجديد)
     */
    public function proposeAppointment(Request $request, ItemRequest $itemRequest): RedirectResponse
    {
        $user = $request->user();
        $appointment = $this->currentAppointment($request, $itemRequest);

        if (! $appointment) {
            return back()->with(['toast' => 'لا يمكن تعديل الموعد في هذه المرحلة', 'toast_type' => 'error']);
        }

        $data = $request->validate([
            'meeting_date' => ['required', 'date', 'after:now'],
            'meeting_location' => ['required', 'string', 'max:150'],
        ], [
            'meeting_date.*' => 'حدد موعداً في المستقبل',
            'meeting_location.*' => 'حدد مكان الاستلام',
        ]);

        DB::transaction(function () use ($appointment, $itemRequest, $data, $user) {
            $appointment->update(['status' => 'canceled']);

            $itemRequest->appointments()->create([
                'proposed_by' => $user->id,
                'meeting_date' => $data['meeting_date'],
                'meeting_location' => $data['meeting_location'],
                'status' => 'new',
            ]);
        });

        $other = $itemRequest->otherParty($user);
        Notification::send($other->id, 'موعد استلام جديد', $user->full_name.' اقترح موعداً جديداً لـ "'.$itemRequest->item->title.'". راجعه وأكده.', route('requests.show', $itemRequest));

        return back()->with('toast', 'تم إرسال الموعد الجديد');
    }

    /**
     * تأكيد الاستلام (الباحث)
     * بيع / تبادل: العملية بتنتهي. تأجير: بتبلش مدة التأجير
     */
    public function confirmReceipt(Request $request, ItemRequest $itemRequest): RedirectResponse
    {
        abort_unless($itemRequest->requester_id === $request->user()->id, 403);

        if ($itemRequest->status !== 'approved') {
            return back()->with(['toast' => 'لا يمكن تأكيد الاستلام في هذه المرحلة', 'toast_type' => 'error']);
        }

        $item = $itemRequest->item;

        DB::transaction(function () use ($itemRequest, $item) {
            if ($item->transaction_type === 'rent') {
                $itemRequest->update(['status' => 'received']);
                $item->update(['state' => 'rented']);
            } else {
                $itemRequest->update(['status' => 'finished']);

                // الكمية بتنقص، واذا خلصت الغرض بيصير "مباع"
                $remaining = max(0, $item->count - $itemRequest->count);
                $item->update(['count' => $remaining, 'state' => $remaining === 0 ? 'sold' : $item->state]);

                // بالتبادل: الغرض المعروض من الباحث كمان بيصير مباع
                $offered = $itemRequest->exchangeOffer?->offeredItem;
                if ($offered) {
                    $offered->update(['state' => 'sold']);
                }
            }
        });

        // الموعد الحالي بيصير مؤكد
        $itemRequest->appointments()->where('status', 'new')->update(['status' => 'confirmed']);

        ActivityLog::record('update', $itemRequest, ['status' => 'approved'], ['status' => $itemRequest->status]);
        Notification::send($item->owner_id, 'تم استلام الغرض', $request->user()->full_name.' أكد استلام "'.$item->title.'".', route('requests.show', $itemRequest));

        $message = $item->transaction_type === 'rent'
            ? 'تم تأكيد الاستلام. لا تنسَ إشعار المالك عند إرجاع الغرض'
            : 'تمت العملية بنجاح! يمكنك الآن تقييم المالك';

        return back()->with('toast', $message);
    }

    /**
     * إشعار المالك بإرجاع الغرض المؤجر (الباحث)
     */
    public function reportReturn(Request $request, ItemRequest $itemRequest): RedirectResponse
    {
        abort_unless($itemRequest->requester_id === $request->user()->id, 403);

        $rental = $itemRequest->rental;

        if ($itemRequest->status !== 'received' || ! $rental || $rental->return_reported_at) {
            return back()->with(['toast' => 'لا يمكن الإبلاغ عن الإرجاع في هذه المرحلة', 'toast_type' => 'error']);
        }

        $rental->update(['return_reported_at' => now(), 'actual_return_date' => today()]);

        Notification::send($itemRequest->ownerId(), 'تم إرجاع الغرض', $request->user()->full_name.' أبلغ عن إرجاع "'.$itemRequest->item->title.'". أكد الإرجاع أو اعترض خلال 48 ساعة، وإلا يُعتبر مُرجعاً تلقائياً.', route('requests.show', $itemRequest));

        return back()->with('toast', 'تم إشعار المالك بالإرجاع');
    }

    /**
     * تأكيد الإرجاع (المالك)
     */
    public function confirmReturn(Request $request, ItemRequest $itemRequest): RedirectResponse
    {
        $this->authorizeOwner($request, $itemRequest);
        $rental = $itemRequest->rental;

        if (! $rental || ! $rental->awaitingOwner()) {
            return back()->with(['toast' => 'لا يوجد إرجاع بانتظار تأكيدك', 'toast_type' => 'error']);
        }

        DB::transaction(function () use ($rental, $itemRequest) {
            $rental->update(['return_status' => 'confirmed']);
            $itemRequest->update(['status' => 'finished']);
            $this->makeAvailableAgain($itemRequest);
        });

        ActivityLog::record('update', $rental, ['return_status' => 'pending'], ['return_status' => 'confirmed']);
        Notification::send($itemRequest->requester_id, 'تم تأكيد الإرجاع', 'أكد المالك إرجاع "'.$itemRequest->item->title.'". يمكنك الآن تقييم المالك.', route('requests.show', $itemRequest));

        return back()->with('toast', 'تم تأكيد الإرجاع، يمكنك الآن تقييم المستأجر');
    }

    /**
     * الاعتراض على الإرجاع (المالك): بيتحول لبلاغ بيراجعه المشرف
     */
    public function disputeReturn(Request $request, ItemRequest $itemRequest): RedirectResponse
    {
        $this->authorizeOwner($request, $itemRequest);
        $rental = $itemRequest->rental;

        if (! $rental || ! $rental->awaitingOwner()) {
            return back()->with(['toast' => 'لا يوجد إرجاع بانتظار تأكيدك', 'toast_type' => 'error']);
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'reason.*' => 'اشرح سبب الاعتراض (10 أحرف على الأقل)',
        ]);

        DB::transaction(function () use ($rental, $itemRequest, $data, $request) {
            $rental->update(['return_status' => 'disputed']);
            $itemRequest->update(['status' => 'finished']);
            $this->makeAvailableAgain($itemRequest);

            DB::table('reports')->insert([
                'reporter_id' => $request->user()->id,
                'request_id' => $itemRequest->id,
                'reason' => 'اعتراض على الإرجاع: '.$data['reason'],
                'status' => 'pending',
                'reported_at' => now(),
                'updated_at' => now(),
            ]);
        });

        ActivityLog::record('update', $rental, ['return_status' => 'pending'], ['return_status' => 'disputed']);
        Notification::send($itemRequest->requester_id, 'اعتراض على الإرجاع', 'اعترض المالك على إرجاع "'.$itemRequest->item->title.'"، وسيراجع المشرف الاعتراض.', route('requests.show', $itemRequest));

        foreach (User::where('is_admin', true)->pluck('id') as $adminId) {
            Notification::send($adminId, 'بلاغ جديد', 'اعتراض على إرجاع "'.$itemRequest->item->title.'" بانتظار المراجعة.');
        }

        return back()->with('toast', 'تم تسجيل الاعتراض وإرساله للمشرف');
    }

    // ===== دوال مساعدة =====

    private function authorizeOwner(Request $request, ItemRequest $itemRequest): void
    {
        abort_unless($itemRequest->ownerId() === $request->user()->id, 403);
    }

    // الموعد الحالي لطلب مقبول (بس للطرفين)
    private function currentAppointment(Request $request, ItemRequest $itemRequest): ?Appointment
    {
        abort_unless($itemRequest->isParty($request->user()), 403);

        if ($itemRequest->status !== 'approved') {
            return null;
        }

        return $itemRequest->appointments()->whereIn('status', ['new', 'confirmed'])->latest('id')->first();
    }

    // بعد الإرجاع الغرض المؤجر بيرجع متاح
    private function makeAvailableAgain(ItemRequest $itemRequest): void
    {
        $item = $itemRequest->item;

        if ($item->state === 'rented') {
            $item->update(['state' => 'active']);
        }
    }
}
