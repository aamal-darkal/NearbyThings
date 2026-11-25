<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Item;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * الإعلانات والطلبات: اطلاع المشرف على العمليات، وحجب الأغراض المخالفة
 */
class ListingController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->input('tab') === 'requests' ? 'requests' : 'items';
        $search = trim((string) $request->input('q'));
        $type = $request->input('type');
        $status = $request->input('status');

        if ($tab === 'items') {
            $rows = Item::with(['owner', 'images', 'category'])
                ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                    ->where('title', 'like', "%{$search}%")
                    ->orWhereHas('owner', fn ($q) => $q->where('full_name', 'like', "%{$search}%"))))
                ->when(in_array($type, ['sale', 'exchange', 'rent']), fn ($q) => $q->where('transaction_type', $type))
                ->when($status, fn ($q) => $q->where('state', $status))
                ->latest()
                ->paginate(10)
                ->withQueryString();
        } else {
            // الطلبات مع الغرض والمالك وصاحب الطلب وتفاصيل التأجير أو التبادل
            $rows = DB::table('requests')
                ->join('items', 'items.id', '=', 'requests.item_id')
                ->join('users as owners', 'owners.id', '=', 'items.owner_id')
                ->join('users as requesters', 'requesters.id', '=', 'requests.requester_id')
                ->leftJoin('rentals', 'rentals.request_id', '=', 'requests.id')
                ->leftJoin('exchange_offers', 'exchange_offers.request_id', '=', 'requests.id')
                ->leftJoin('items as offered', 'offered.id', '=', 'exchange_offers.offered_item_id')
                ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                    ->where('items.title', 'like', "%{$search}%")
                    ->orWhere('owners.full_name', 'like', "%{$search}%")
                    ->orWhere('requesters.full_name', 'like', "%{$search}%")))
                ->when(in_array($type, ['sale', 'exchange', 'rent']), fn ($q) => $q->where('items.transaction_type', $type))
                ->when($status, fn ($q) => $q->where('requests.status', $status))
                ->orderByDesc('requests.request_date')
                ->select([
                    'requests.id', 'requests.status', 'requests.request_date',
                    'items.id as item_id', 'items.title', 'items.transaction_type', 'items.price', 'items.rent_unit',
                    'owners.full_name as owner_name', 'requesters.full_name as requester_name',
                    'rentals.start_date', 'rentals.expected_return_date',
                    DB::raw('COALESCE(offered.title, exchange_offers.offered_description) as offer'),
                ])
                ->paginate(10)
                ->withQueryString();
        }

        return view('admin.listings', [
            'tab' => $tab,
            'rows' => $rows,
            'itemsCount' => Item::count(),
            'requestsCount' => DB::table('requests')->count(),
        ]);
    }

    /**
     * حجب غرض مخالف (ما بيظهر بالبحث ولا بيقدر صاحبه يعدله)
     */
    public function block(Item $item): RedirectResponse
    {
        if ($item->state === 'banned') {
            return back()->with('toast', 'الإعلان محجوب مسبقاً');
        }

        $old = $item->state;
        $item->update(['state' => 'banned']);

        ActivityLog::record('update', $item, ['state' => $old], ['state' => 'banned']);
        Notification::send($item->owner_id, 'تم حجب إعلانك', 'تم حجب إعلان "'.$item->title.'" لمخالفته سياسة الاستخدام.');

        return back()->with('toast', 'تم حجب الإعلان');
    }

    /**
     * إلغاء حجب غرض (بيرجع موقوف مؤقتاً، وصاحبه بيفعله)
     */
    public function unblock(Item $item): RedirectResponse
    {
        if ($item->state !== 'banned') {
            return back();
        }

        $item->update(['state' => 'inactive']);

        ActivityLog::record('update', $item, ['state' => 'banned'], ['state' => 'inactive']);
        Notification::send($item->owner_id, 'تم إلغاء حجب إعلانك', 'أصبح بإمكانك إعادة تفعيل إعلان "'.$item->title.'" من صفحة أغراضي.');

        return back()->with('toast', 'تم إلغاء حجب الإعلان');
    }
}
