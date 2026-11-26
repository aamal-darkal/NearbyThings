<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * مراجعة البلاغات من المشرف: حظر الطرف المُبلَّغ عنه، حجب الغرض، أو رفض البلاغ
 */
class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $status = in_array($request->input('status'), ['pending', 'resolved', 'rejected']) ? $request->input('status') : null;
        $category = array_key_exists((string) $request->input('category'), Report::CATEGORIES) ? $request->input('category') : null;

        $reports = Report::with(['reporter', 'item.owner', 'request.item.owner', 'request.requester'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($category, fn ($q) => $q->where('category', $category))
            ->orderByRaw("status = 'pending' desc")
            ->latest('reported_at')
            ->paginate(10)
            ->withQueryString();

        // البلاغ المختار (لوحة جانبية)
        $selected = $request->filled('report')
            ? Report::with(['reporter', 'item.owner', 'request.item.owner', 'request.requester', 'request.rental'])->find($request->input('report'))
            : null;

        return view('admin.reports', [
            'reports' => $reports,
            'selected' => $selected,
            'pendingCount' => Report::where('status', 'pending')->count(),
            'categories' => Report::CATEGORIES,
        ]);
    }

    /**
     * اتخاذ إجراء على البلاغ
     * ban_user: حظر الطرف المُبلَّغ عنه / block_item: حجب الغرض / reject: رفض البلاغ
     */
    public function resolve(Request $request, Report $report): RedirectResponse
    {
        if ($report->status !== 'pending') {
            return back()->with(['toast' => 'تمت مراجعة هذا البلاغ مسبقاً', 'toast_type' => 'error']);
        }

        $data = $request->validate([
            'action' => ['required', Rule::in(['ban_user', 'block_item', 'reject'])],
            'admin_note' => ['nullable', 'string', 'max:500'],
        ], [
            'action.*' => 'اختر الإجراء',
            'admin_note.*' => 'الملاحظة 500 حرف كحد أقصى',
        ]);

        $item = $report->relatedItem();
        $reportedUser = $report->reportedUser();
        $note = $data['admin_note'] ?? null;

        if ($data['action'] === 'ban_user' && $reportedUser) {
            if ($reportedUser->is_admin) {
                return back()->with(['toast' => 'لا يمكن حظر حساب مشرف', 'toast_type' => 'error']);
            }

            $reportedUser->forceFill(['is_blocked' => true])->save();
            $reportedUser->tokens()->delete();
            ActivityLog::record('update', $reportedUser, ['is_blocked' => false], ['is_blocked' => true, 'report_id' => $report->id]);
            Notification::send($reportedUser->id, 'تم حظر حسابك', 'تم حظر حسابك بعد مراجعة بلاغ مقدّم ضدك.'.($note ? ' ملاحظة الإدارة: '.$note : ''));
        }

        if ($data['action'] === 'block_item' && $item && $item->state !== 'banned') {
            $old = $item->state;
            $item->update(['state' => 'banned']);
            ActivityLog::record('update', $item, ['state' => $old], ['state' => 'banned', 'report_id' => $report->id]);
            Notification::send($item->owner_id, 'تم حجب إعلانك', 'تم حجب إعلان "'.$item->title.'" بعد مراجعة بلاغ مقدّم عليه.');
        }

        $report->update([
            'status' => $data['action'] === 'reject' ? 'rejected' : 'resolved',
            'admin_note' => $note,
        ]);

        ActivityLog::record('update', $report, ['status' => 'pending'], ['status' => $report->status, 'action' => $data['action']]);

        $message = $data['action'] === 'reject'
            ? 'راجع المشرف بلاغك ولم يجد مخالفة تستدعي إجراءً.'
            : 'راجع المشرف بلاغك واتخذ الإجراء المناسب. شكراً لمساعدتك.';
        Notification::send($report->reporter_id, 'تمت مراجعة بلاغك', $message);

        return redirect()->route('admin.reports.index')->with('toast', $data['action'] === 'reject' ? 'تم رفض البلاغ' : 'تم اتخاذ الإجراء على البلاغ');
    }
}
