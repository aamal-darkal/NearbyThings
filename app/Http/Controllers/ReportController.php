<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Item;
use App\Models\ItemRequest;
use App\Models\Notification;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * الإبلاغ عن مخالفة: على غرض (من صفحة الغرض) أو على عملية (من صفحة الطلب)
 */
class ReportController extends Controller
{
    /**
     * نموذج الإبلاغ
     */
    public function create(Request $request): View
    {
        [$item, $itemRequest] = $this->target($request);

        return view('reports.create', [
            'item' => $item,
            'itemRequest' => $itemRequest,
            'categories' => Report::CATEGORIES,
        ]);
    }

    /**
     * حفظ البلاغ وإشعار المشرفين
     */
    public function store(Request $request): RedirectResponse
    {
        [$item, $itemRequest] = $this->target($request);
        $user = $request->user();

        $data = $request->validate([
            'category' => ['required', Rule::in(array_keys(Report::CATEGORIES))],
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'category.*' => 'اختر سبب الإبلاغ',
            'reason.*' => 'اكتب تفاصيل البلاغ (10 أحرف على الأقل)',
        ]);

        // بلاغ واحد قيد المراجعة لكل مستخدم على نفس الغرض أو الطلب
        $duplicate = Report::where('reporter_id', $user->id)
            ->where('status', 'pending')
            ->where('item_id', $itemRequest ? null : $item->id)
            ->where('request_id', $itemRequest?->id)
            ->exists();

        if ($duplicate) {
            return redirect()->route('items.show', $item)->with(['toast' => 'لديك بلاغ قيد المراجعة على هذا الغرض', 'toast_type' => 'error']);
        }

        $report = Report::create([
            'reporter_id' => $user->id,
            'item_id' => $itemRequest ? null : $item->id,
            'request_id' => $itemRequest?->id,
            'category' => $data['category'],
            'reason' => $data['reason'],
            'status' => 'pending',
        ]);

        ActivityLog::record('create', $report, null, ['category' => $data['category']]);

        foreach (User::where('is_admin', true)->pluck('id') as $adminId) {
            Notification::send($adminId, 'بلاغ جديد', 'بلاغ جديد ('.$report->categoryLabel().') على "'.$item->title.'".', route('admin.reports.index', ['report' => $report->id]));
        }

        $back = $itemRequest ? route('requests.show', $itemRequest) : route('items.show', $item);

        return redirect($back)->with('toast', 'تم إرسال البلاغ، سيراجعه المشرف ويتخذ الإجراء المناسب');
    }

    // الغرض أو الطلب المُبلَّغ عنه (من الرابط: ?item= أو ?request=)
    private function target(Request $request): array
    {
        $user = $request->user();

        if ($request->filled('request')) {
            $itemRequest = ItemRequest::with('item.owner')->findOrFail($request->input('request'));
            abort_unless($itemRequest->isParty($user), 403);

            return [$itemRequest->item, $itemRequest];
        }

        $item = Item::with('owner')->findOrFail($request->input('item'));
        abort_if($item->owner_id === $user->id, 403, 'لا يمكنك الإبلاغ عن غرضك');

        return [$item, null];
    }
}
