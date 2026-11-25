<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * لوحة تحكم المشرف: مؤشرات وإحصائيات عامة
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        // ===== بطاقات الأرقام =====
        $stats = [
            'items' => Item::count(),
            'items_week' => Item::where('created_at', '>=', now()->subWeek())->count(),
            'active_requests' => DB::table('requests')->whereIn('status', ['waiting', 'approved', 'received'])->count(),
            'open_reports' => DB::table('reports')->where('status', 'pending')->count(),
            'users' => User::count(),
            'users_month' => User::where('created_at', '>=', now()->subMonth())->count(),
        ];

        // ===== توزيع الإعلانات حسب النوع =====
        $byType = Item::select('transaction_type', DB::raw('count(*) as total'))
            ->groupBy('transaction_type')
            ->pluck('total', 'transaction_type');

        $types = [];
        foreach (['sale' => 'بيع', 'rent' => 'تأجير', 'exchange' => 'تبادل'] as $key => $label) {
            $count = (int) ($byType[$key] ?? 0);
            $types[$key] = [
                'label' => $label,
                'count' => $count,
                'percent' => $stats['items'] > 0 ? round($count * 100 / $stats['items']) : 0,
            ];
        }

        // ===== الإعلانات الجديدة خلال آخر 30 يوماً (عدد لكل يوم) =====
        $from = now()->subDays(29)->startOfDay();
        $perDay = Item::where('created_at', '>=', $from)
            ->get(['created_at'])
            ->groupBy(fn ($item) => $item->created_at->toDateString())
            ->map->count();

        $daily = [];
        for ($i = 0; $i < 30; $i++) {
            $day = $from->copy()->addDays($i)->toDateString();
            $daily[] = (int) ($perDay[$day] ?? 0);
        }

        // ===== أحدث البلاغات المفتوحة =====
        $latestReports = DB::table('reports')
            ->join('users as reporters', 'reporters.id', '=', 'reports.reporter_id')
            ->leftJoin('items', 'items.id', '=', 'reports.item_id')
            ->leftJoin('requests', 'requests.id', '=', 'reports.request_id')
            ->leftJoin('items as request_items', 'request_items.id', '=', 'requests.item_id')
            ->where('reports.status', 'pending')
            ->orderByDesc('reports.reported_at')
            ->limit(5)
            ->get([
                'reports.id', 'reports.reason', 'reports.reported_at',
                'reporters.full_name as reporter_name', 'reporters.address as reporter_area',
                DB::raw('COALESCE(items.title, request_items.title) as item_title'),
            ]);

        // ===== اقتراحات التصنيفات =====
        $suggestions = Category::where('state', 'new')
            ->withCount('items')
            ->latest()
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'types' => $types,
            'daily' => $daily,
            'latestReports' => $latestReports,
            'suggestions' => $suggestions,
        ]);
    }
}
