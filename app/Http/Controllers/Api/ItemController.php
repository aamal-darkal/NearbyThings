<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Support\ItemSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * البحث عن الأغراض القريبة عبر الـ API (للاختبار من Postman)
 * مثال: GET /api/items?q=دراجة&type=rent&radius=5&lat=33.51&lng=36.27
 */
class ItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $center = ItemSearch::center($request);
        $radius = min(max((int) $request->input('radius', 5), 1), 50);

        $query = Item::visible()->with(['images', 'category']);

        if ($request->filled('q')) {
            $query->where('title', 'like', '%'.$request->q.'%');
        }
        if (in_array($request->type, ['sale', 'exchange', 'rent'])) {
            $query->where('transaction_type', $request->type);
        }

        $items = ItemSearch::withDistance($query, $center['lat'], $center['lng'], $radius)->take(50);

        // الموقع تقريبي فقط (منزلتين = حوالي 1 كم)، وما في بيانات تواصل
        return response()->json([
            'center' => ['lat' => $center['lat'], 'lng' => $center['lng']],
            'radius_km' => $radius,
            'count' => $items->count(),
            'items' => $items->map(fn (Item $item) => [
                'id' => $item->id,
                'title' => $item->title,
                'type' => $item->transaction_type,
                'price' => $item->priceLabel(),
                'category' => $item->category->category_name,
                'area' => $item->location_hint,
                'distance' => $item->distanceLabel(),
                'approx_lat' => round($item->latitude, 2),
                'approx_lng' => round($item->longitude, 2),
                'image' => $item->mainImageUrl(),
                'url' => route('items.show', $item),
            ])->values(),
        ]);
    }
}
