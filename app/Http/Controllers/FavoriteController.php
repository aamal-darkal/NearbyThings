<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * المفضلة: حفظ الإعلانات للرجوع إليها
 */
class FavoriteController extends Controller
{
    /**
     * صفحة المفضلة (مع فلترة حسب نوع العرض)
     */
    public function index(Request $request): View
    {
        $favorites = $request->user()->favoriteItems()
            ->with(['images', 'owner'])
            ->orderByPivot('created_at', 'desc')
            ->get();

        // الأعداد لكل نوع
        $counts = [
            'all' => $favorites->count(),
            'sale' => $favorites->where('transaction_type', 'sale')->count(),
            'rent' => $favorites->where('transaction_type', 'rent')->count(),
            'exchange' => $favorites->where('transaction_type', 'exchange')->count(),
        ];

        $type = $request->input('type');
        if (in_array($type, ['sale', 'rent', 'exchange'])) {
            $favorites = $favorites->where('transaction_type', $type);
        }

        return view('favorites.index', [
            'favorites' => $favorites,
            'counts' => $counts,
            'type' => $type,
        ]);
    }

    /**
     * إضافة أو إزالة غرض من المفضلة
     * بيرجع JSON اذا الطلب من JavaScript، وإلا بيرجع للصفحة السابقة
     */
    public function toggle(Request $request, Item $item): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $isFavorite = $user->favoriteItems()->whereKey($item->id)->exists();

        if ($isFavorite) {
            $user->favoriteItems()->detach($item->id);
            $message = 'تمت إزالة الغرض من المفضلة';
        } elseif ($item->owner_id === $user->id) {
            return $this->respond($request, false, 'لا يمكنك إضافة غرضك إلى المفضلة', 422);
        } elseif ($item->state !== 'active') {
            return $this->respond($request, false, 'هذا الغرض لم يعد متاحاً', 422);
        } else {
            $user->favoriteItems()->attach($item->id, ['created_at' => now()]);
            $message = 'تمت إضافة الغرض إلى المفضلة';
        }

        return $this->respond($request, ! $isFavorite, $message);
    }

    // الرد المناسب حسب نوع الطلب
    private function respond(Request $request, bool $favorited, string $message, int $status = 200): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['favorited' => $favorited, 'message' => $message], $status);
        }

        return back()->with([
            'toast' => $message,
            'toast_type' => $status === 200 ? null : 'error',
        ]);
    }
}
