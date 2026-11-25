<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * المستخدمون: استعراض وحظر المخالفين
 */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q'));
        $status = $request->input('status');

        $users = User::query()
            ->withCount(['items as active_items_count' => fn ($q) => $q->where('state', 'active')])
            ->addSelect(['reports_count' => $this->reportsAgainstQuery()])
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('full_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%")))
            ->when($status === 'blocked', fn ($q) => $q->where('is_blocked', true))
            ->when($status === 'active', fn ($q) => $q->where('is_blocked', false))
            ->orderByDesc('is_admin')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // ملف المستخدم المختار (لوحة جانبية)
        $selected = null;
        $selectedReports = collect();
        $completedDeals = 0;

        if ($request->filled('user')) {
            $selected = User::withCount([
                'items as active_items_count' => fn ($q) => $q->where('state', 'active'),
            ])->find($request->input('user'));

            if ($selected) {
                $selectedReports = $this->reportsAgainst($selected);
                $completedDeals = DB::table('requests')
                    ->join('items', 'items.id', '=', 'requests.item_id')
                    ->where('requests.status', 'finished')
                    ->where(fn ($q) => $q->where('items.owner_id', $selected->id)->orWhere('requests.requester_id', $selected->id))
                    ->count();
            }
        }

        return view('admin.users', [
            'users' => $users,
            'selected' => $selected,
            'selectedReports' => $selectedReports,
            'completedDeals' => $completedDeals,
            'totalUsers' => User::count(),
        ]);
    }

    /**
     * حظر مستخدم: بيطلع من حسابه، وإعلاناته بتختفي من البحث
     */
    public function ban(Request $request, User $user): RedirectResponse
    {
        if ($user->is_admin) {
            return back()->with(['toast' => 'لا يمكن حظر حساب مشرف', 'toast_type' => 'error']);
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'reason.*' => 'اكتب سبب الحظر (5 أحرف على الأقل)',
        ]);

        $user->forceFill(['is_blocked' => true])->save();

        // تسجيل خروجه من كل الأجهزة (توكنات الـ API)
        $user->tokens()->delete();

        ActivityLog::record('update', $user, ['is_blocked' => false], ['is_blocked' => true, 'reason' => $data['reason']]);
        Notification::send($user->id, 'تم حظر حسابك', 'تم حظر حسابك من قبل الإدارة. السبب: '.$data['reason']);

        return back()->with('toast', 'تم حظر المستخدم');
    }

    public function unban(User $user): RedirectResponse
    {
        $user->forceFill(['is_blocked' => false])->save();

        ActivityLog::record('update', $user, ['is_blocked' => true], ['is_blocked' => false]);
        Notification::send($user->id, 'تم رفع الحظر عن حسابك', 'يمكنك الآن استخدام حسابك بشكل طبيعي.');

        return back()->with('toast', 'تم رفع الحظر عن المستخدم');
    }

    // ===== البلاغات المسجلة ضد مستخدم =====
    // بلاغ على غرض بيملكه، أو بلاغ على طلب هو طرف فيه (وما هو اللي بلّغ)

    private function reportsAgainstQuery()
    {
        return DB::table('reports')
            ->selectRaw('count(*)')
            ->leftJoin('items', 'items.id', '=', 'reports.item_id')
            ->leftJoin('requests', 'requests.id', '=', 'reports.request_id')
            ->leftJoin('items as request_items', 'request_items.id', '=', 'requests.item_id')
            ->where(fn ($q) => $q
                ->whereColumn('items.owner_id', 'users.id')
                ->orWhere(fn ($q) => $q
                    ->whereColumn('reports.reporter_id', '!=', 'users.id')
                    ->where(fn ($q) => $q
                        ->whereColumn('requests.requester_id', 'users.id')
                        ->orWhereColumn('request_items.owner_id', 'users.id'))));
    }

    private function reportsAgainst(User $user)
    {
        return DB::table('reports')
            ->join('users as reporters', 'reporters.id', '=', 'reports.reporter_id')
            ->leftJoin('items', 'items.id', '=', 'reports.item_id')
            ->leftJoin('requests', 'requests.id', '=', 'reports.request_id')
            ->leftJoin('items as request_items', 'request_items.id', '=', 'requests.item_id')
            ->where(fn ($q) => $q
                ->where('items.owner_id', $user->id)
                ->orWhere(fn ($q) => $q
                    ->where('reports.reporter_id', '!=', $user->id)
                    ->where(fn ($q) => $q
                        ->where('requests.requester_id', $user->id)
                        ->orWhere('request_items.owner_id', $user->id))))
            ->orderByDesc('reports.reported_at')
            ->limit(5)
            ->get(['reports.id', 'reports.reason', 'reports.status', 'reports.reported_at', 'reporters.full_name as reporter_name']);
    }
}
