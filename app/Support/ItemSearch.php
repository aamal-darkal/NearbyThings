<?php

namespace App\Support;

use App\Models\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * البحث عن الأغراض القريبة: تحديد نقطة البحث وحساب المسافة لكل غرض
 */
class ItemSearch
{
    // مركز دمشق (اذا ما في موقع للمستخدم)
    public const DEFAULT_CENTER = [33.5138, 36.2765];

    /**
     * نقطة البحث: من الرابط (زر حددني)، أو موقع المستخدم، أو مركز المدينة
     *
     * @return array{lat: float, lng: float, label: string, source: string}
     */
    public static function center(Request $request): array
    {
        if ($request->filled(['lat', 'lng']) && is_numeric($request->lat) && is_numeric($request->lng)) {
            return [
                'lat' => (float) $request->lat,
                'lng' => (float) $request->lng,
                'label' => 'موقعي الحالي',
                'source' => 'device',
            ];
        }

        $user = $request->user();
        if ($user && $user->hasLocation()) {
            return [
                'lat' => $user->latitude,
                'lng' => $user->longitude,
                'label' => $user->areaLabel() ?: 'موقعي',
                'source' => 'account',
            ];
        }

        return [
            'lat' => self::DEFAULT_CENTER[0],
            'lng' => self::DEFAULT_CENTER[1],
            'label' => 'دمشق',
            'source' => 'default',
        ];
    }

    /**
     * بيرجع الأغراض مع المسافة عن النقطة، مرتبة من الأقرب
     * اذا انحدد نصف قطر، الأغراض الأبعد (أو بدون موقع) ما بترجع
     */
    public static function withDistance(Builder $query, float $lat, float $lng, ?float $radiusKm = null): Collection
    {
        // فلترة أولية بمربع حول النقطة (أسرع من حساب المسافة لكل الأغراض)
        if ($radiusKm !== null) {
            $latDelta = $radiusKm / 111;
            $lngDelta = $radiusKm / (111 * max(cos(deg2rad($lat)), 0.01));

            $query->whereBetween('latitude', [$lat - $latDelta, $lat + $latDelta])
                ->whereBetween('longitude', [$lng - $lngDelta, $lng + $lngDelta]);
        }

        $items = $query->get()->each(function (Item $item) use ($lat, $lng) {
            if ($item->latitude !== null && $item->longitude !== null) {
                $item->distance = Geo::distanceKm($lat, $lng, $item->latitude, $item->longitude);
            }
        });

        if ($radiusKm !== null) {
            $items = $items->filter(fn (Item $item) => $item->distance !== null && $item->distance <= $radiusKm);
        }

        // الأغراض بدون موقع بتجي بالآخر
        return $items->sortBy(fn (Item $item) => $item->distance ?? PHP_FLOAT_MAX)->values();
    }
}
