<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * الإشعارات: القائمة، فتح إشعار، وتعليم الكل كمقروء
 */
class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $filter = $request->input('filter') === 'unread' ? 'unread' : 'all';

        $notifications = $user->siteNotifications()
            ->when($filter === 'unread', fn ($q) => $q->where('is_read', false))
            ->latest()
            ->limit(100)
            ->get();

        // تجميع حسب الفترة: اليوم / أمس / هذا الأسبوع / أقدم
        $groups = $notifications->groupBy(function (Notification $notification) {
            $date = $notification->created_at;

            return match (true) {
                $date->isToday() => 'اليوم',
                $date->isYesterday() => 'أمس',
                $date->greaterThanOrEqualTo(now()->subWeek()) => 'هذا الأسبوع',
                default => 'أقدم',
            };
        });

        return view('notifications.index', [
            'groups' => $groups,
            'filter' => $filter,
            'totalCount' => $user->siteNotifications()->count(),
            'unreadCount' => $user->siteNotifications()->where('is_read', false)->count(),
        ]);
    }

    /**
     * فتح إشعار: بيصير مقروء وبينقل للصفحة المرتبطة
     */
    public function open(Request $request, Notification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->update(['is_read' => true]);

        // بس روابط من نفس الموقع
        $link = $notification->link && str_starts_with($notification->link, url('/')) ? $notification->link : route('notifications.index');

        return redirect($link);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->siteNotifications()->where('is_read', false)->update(['is_read' => true]);

        return back()->with('toast', 'تم تعليم كل الإشعارات كمقروءة');
    }
}
