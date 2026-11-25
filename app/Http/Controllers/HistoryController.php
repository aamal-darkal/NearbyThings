<?php

namespace App\Http\Controllers;

use App\Models\ItemRequest;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * سجل التعاملات: كل العمليات المنتهية للمستخدم كمالك أو كباحث
 */
class HistoryController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $type = in_array($request->input('type'), ['sale', 'exchange', 'rent']) ? $request->input('type') : null;
        $role = in_array($request->input('role'), ['owner', 'seeker']) ? $request->input('role') : null;
        $months = in_array((int) $request->input('months'), [1, 6, 12]) ? (int) $request->input('months') : null;

        $query = ItemRequest::involving($user)
            ->whereIn('status', ['finished', 'rejected', 'canceled'])
            ->with(['item.images', 'item.owner', 'requester'])
            ->when($type, fn ($q) => $q->whereHas('item', fn ($q) => $q->where('transaction_type', $type)))
            ->when($role === 'owner', fn ($q) => $q->whereHas('item', fn ($q) => $q->where('owner_id', $user->id)))
            ->when($role === 'seeker', fn ($q) => $q->where('requester_id', $user->id))
            ->when($months, fn ($q) => $q->where('updated_at', '>=', now()->subMonths($months)))
            ->latest('updated_at');

        $deals = $query->paginate(10)->withQueryString();

        // تقييماتي على هالعمليات (لعرض "قيّم الآن" أو التقييم اللي أعطيته)
        $myReviews = Review::where('reviewer_id', $user->id)
            ->whereIn('request_id', $deals->pluck('id'))
            ->pluck('rating', 'request_id');

        return view('history.index', [
            'deals' => $deals,
            'myReviews' => $myReviews,
            'user' => $user,
            'filters' => compact('type', 'role', 'months'),
            'successCount' => ItemRequest::involving($user)->where('status', 'finished')->count(),
        ]);
    }
}
