<?php

namespace App\Support;

use Carbon\CarbonInterface;

/**
 * تواريخ بالعربي بأسماء الأشهر المستخدمة في سوريا
 */
class Dates
{
    private const MONTHS = [
        1 => 'كانون الثاني', 2 => 'شباط', 3 => 'آذار', 4 => 'نيسان', 5 => 'أيار', 6 => 'حزيران',
        7 => 'تموز', 8 => 'آب', 9 => 'أيلول', 10 => 'تشرين الأول', 11 => 'تشرين الثاني', 12 => 'كانون الأول',
    ];

    // "نيسان 2026"
    public static function monthYear(?CarbonInterface $date): string
    {
        return $date ? self::MONTHS[$date->month].' '.$date->year : '';
    }

    // "25 تشرين الثاني 2026"
    public static function full(?CarbonInterface $date): string
    {
        return $date ? $date->day.' '.self::MONTHS[$date->month].' '.$date->year : '';
    }

    // "25 تشرين الثاني، 5:30 م"
    public static function dateTime(?CarbonInterface $date): string
    {
        if (! $date) {
            return '';
        }

        $period = $date->hour < 12 ? 'ص' : 'م';
        $hour = $date->hour % 12 ?: 12;

        return $date->day.' '.self::MONTHS[$date->month].'، '.$hour.':'.$date->format('i').' '.$period;
    }

    // "منذ 3 أيام"
    public static function ago(?CarbonInterface $date): string
    {
        if (! $date) {
            return '';
        }

        $minutes = (int) abs($date->diffInMinutes(now()));

        if ($minutes < 1) {
            return 'الآن';
        }
        if ($minutes < 60) {
            return 'منذ '.self::count($minutes, 'دقيقة', 'دقيقتين', 'دقائق');
        }

        $hours = intdiv($minutes, 60);
        if ($hours < 24) {
            return 'منذ '.self::count($hours, 'ساعة', 'ساعتين', 'ساعات');
        }

        $days = intdiv($hours, 24);
        if ($days < 7) {
            return $days === 1 ? 'أمس' : 'منذ '.self::count($days, 'يوم', 'يومين', 'أيام');
        }
        if ($days < 30) {
            return 'منذ '.self::count(intdiv($days, 7), 'أسبوع', 'أسبوعين', 'أسابيع');
        }
        if ($days < 365) {
            return 'منذ '.self::count(intdiv($days, 30), 'شهر', 'شهرين', 'أشهر');
        }

        return 'منذ '.self::count(intdiv($days, 365), 'سنة', 'سنتين', 'سنوات');
    }

    // العدد مع الكلمة: "يوم" / "يومين" / "3 أيام" / "11 يوم"
    public static function count(int $n, string $one, string $two, string $few): string
    {
        return match (true) {
            $n === 1 => $one,
            $n === 2 => $two,
            $n <= 10 => $n.' '.$few,
            default => $n.' '.$one,
        };
    }
}
