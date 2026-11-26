<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Support\Geo;
use App\Support\ItemSearch;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * الصفحة الرئيسية: التصنيفات، الأغراض القريبة، وأحدث الإعلانات
 */
class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $center = ItemSearch::center($request);

        // التصنيفات المعتمدة مع عدد الأغراض الظاهرة بكل تصنيف
        $categories = Category::active()
            ->withCount(['items' => fn ($q) => $q->visible()])
            ->orderByDesc('items_count')
            ->take(8)
            ->get();

        // الأقرب للمستخدم (ضمن 20 كم)
        $nearby = ItemSearch::withDistance(
            Item::visible()->with(['images', 'owner']),
            $center['lat'],
            $center['lng'],
            20,
        )->take(4);

        // أحدث الإعلانات مع المسافة
        $latest = Item::visible()->with(['images', 'owner'])->latest()->take(8)->get();
        $latest->each(function (Item $item) use ($center) {
            if ($item->latitude !== null) {
                $item->distance = Geo::distanceKm($center['lat'], $center['lng'], $item->latitude, $item->longitude);
            }
        });

        return view('home', [
            'categories' => $categories,
            'nearby' => $nearby,
            'latest' => $latest,
            'center' => $center,
        ]);
    }
}
