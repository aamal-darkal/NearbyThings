<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Support\Geo;
use App\Support\ItemSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * صفحة تفاصيل الغرض (للجميع)
 */
class ItemController extends Controller
{
    public function show(Request $request, Item $item): View
    {
        $item->load(['images', 'owner', 'category', 'condition']);
        $viewer = $request->user();
        $isOwner = $viewer && $viewer->id === $item->owner_id;
        $isAdmin = $viewer && $viewer->is_admin;

        // الغرض المحجوب أو صاحبه محظور: بس المالك والمشرف بيشوفوه
        if (($item->state === 'banned' || $item->owner->is_blocked) && ! $isOwner && ! $isAdmin) {
            abort(404);
        }

        // المسافة عن المستخدم
        $center = ItemSearch::center($request);
        if ($item->latitude !== null) {
            $item->distance = Geo::distanceKm($center['lat'], $center['lng'], $item->latitude, $item->longitude);
        }

        // إحصائيات المالك: الصفقات المكتملة وعدد التقييمات
        $ownerStats = [
            'deals' => DB::table('requests')
                ->join('items', 'items.id', '=', 'requests.item_id')
                ->where('items.owner_id', $item->owner_id)
                ->where('requests.status', 'finished')
                ->count(),
            'reviews' => DB::table('reviews')->where('reviewed_user_id', $item->owner_id)->count(),
        ];

        // أغراض قريبة من نفس التصنيف
        $related = ItemSearch::withDistance(
            Item::visible()->with(['images', 'owner'])
                ->where('category_id', $item->category_id)
                ->whereKeyNot($item->id),
            $center['lat'],
            $center['lng'],
            20,
        )->take(4);

        return view('items.show', [
            'item' => $item,
            'isOwner' => $isOwner,
            'ownerStats' => $ownerStats,
            'related' => $related,
            // الموقع التقريبي للخريطة (دائرة، مو نقطة دقيقة)
            'approxLat' => $item->latitude !== null ? round($item->latitude, 2) : null,
            'approxLng' => $item->longitude !== null ? round($item->longitude, 2) : null,
        ]);
    }
}
