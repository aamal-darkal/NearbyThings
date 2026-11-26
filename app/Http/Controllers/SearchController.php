<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Condition;
use App\Models\Item;
use App\Support\ItemSearch;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

/**
 * البحث والفلترة: كلمات مفتاحية، تصنيف، نوع العملية، السعر، الحالة، والمسافة
 */
class SearchController extends Controller
{
    // نطاقات المسافة المتاحة (كم)
    public const RADIUS_OPTIONS = [1, 5, 10, 20];

    public function index(Request $request): View
    {
        $center = ItemSearch::center($request);

        // ===== قراءة الفلاتر =====
        $keyword = trim((string) $request->input('q'));
        $types = array_values(array_intersect((array) $request->input('types', []), ['sale', 'rent', 'exchange']));
        $radius = in_array((int) $request->input('radius'), self::RADIUS_OPTIONS) ? (int) $request->input('radius') : 5;
        $categoryId = $request->integer('category') ?: null;
        $conditionIds = array_filter(array_map('intval', (array) $request->input('conditions', [])));
        $minPrice = is_numeric($request->input('min_price')) ? (float) $request->input('min_price') : null;
        $maxPrice = is_numeric($request->input('max_price')) ? (float) $request->input('max_price') : null;
        $sort = in_array($request->input('sort'), ['nearest', 'newest', 'price_asc', 'price_desc']) ? $request->input('sort') : 'nearest';

        // ===== الاستعلام الأساسي (بدون فلتر النوع حتى نحسب العدد لكل نوع) =====
        $query = Item::visible()->with(['images', 'owner', 'category']);

        if ($keyword !== '') {
            $query->where(fn ($q) => $q
                ->where('title', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%"));
        }

        // فلتر التصنيف: بس التصنيفات المعتمدة (المقترحة ما بتظهر بالفلاتر قبل الموافقة)
        if ($categoryId) {
            $query->where('category_id', $categoryId)
                ->whereHas('category', fn ($q) => $q->where('state', 'active'));
        }

        if ($conditionIds) {
            $query->whereIn('condition_id', $conditionIds);
        }

        // السعر: التبادل ما إله سعر، فبيضل ظاهر
        if ($minPrice !== null) {
            $query->where(fn ($q) => $q->where('transaction_type', 'exchange')->orWhere('price', '>=', $minPrice));
        }
        if ($maxPrice !== null) {
            $query->where(fn ($q) => $q->where('transaction_type', 'exchange')->orWhere('price', '<=', $maxPrice));
        }

        $results = ItemSearch::withDistance($query, $center['lat'], $center['lng'], $radius);

        // العدد لكل نوع (قبل فلتر النوع)
        $typeCounts = [
            'sale' => $results->where('transaction_type', 'sale')->count(),
            'rent' => $results->where('transaction_type', 'rent')->count(),
            'exchange' => $results->where('transaction_type', 'exchange')->count(),
        ];

        if ($types) {
            $results = $results->whereIn('transaction_type', $types)->values();
        }

        // ===== الترتيب =====
        $results = match ($sort) {
            'newest' => $results->sortByDesc('created_at')->values(),
            'price_asc' => $results->sortBy('price')->values(),
            'price_desc' => $results->sortByDesc('price')->values(),
            default => $results,
        };

        // ===== تقسيم الصفحات (9 بكل صفحة) =====
        $perPage = 9;
        $page = max(1, $request->integer('page', 1));
        $paginator = new LengthAwarePaginator(
            $results->forPage($page, $perPage)->values(),
            $results->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        return view('search.index', [
            'items' => $paginator,
            'center' => $center,
            'filters' => compact('keyword', 'types', 'radius', 'categoryId', 'conditionIds', 'minPrice', 'maxPrice', 'sort'),
            'typeCounts' => $typeCounts,
            'categories' => Category::active()->orderBy('category_name')->get(),
            'conditions' => Condition::orderBy('order')->get(),
            'radiusOptions' => self::RADIUS_OPTIONS,
        ]);
    }
}
