<?php

namespace App\Support;

/**
 * دوال المسافة والموقع التقريبي
 */
class Geo
{
    // المسافة بالكيلومتر بين نقطتين (معادلة Haversine)
    public static function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    // نص المسافة: "800 متر" أو "1.2 كم"
    public static function label(?float $km): string
    {
        if ($km === null) {
            return '';
        }

        if ($km < 1) {
            // تقريب لأقرب 50 متر
            $meters = max(50, (int) (round($km * 1000 / 50) * 50));

            return $meters.' متر';
        }

        return rtrim(rtrim(number_format($km, 1), '0'), '.').' كم';
    }

    // تقريب الإحداثيات لحماية الخصوصية (3 منازل = حوالي 100 متر)
    public static function approximate(?float $value): ?float
    {
        return $value === null ? null : round($value, 3);
    }
}
