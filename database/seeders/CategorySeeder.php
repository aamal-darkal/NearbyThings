<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Reference data: item categories with their icons. Safe to run multiple times.
     */
    public function run(): void
    {
        // اسم التصنيف => الأيقونة
        $categories = [
            'إلكترونيات' => '🔌',
            'هواتف وأجهزة لوحية' => '📱',
            'أجهزة منزلية' => '🧺',
            'أثاث' => '🛋️',
            'ملابس وأحذية' => '👕',
            'كتب وقرطاسية' => '📚',
            'أدوات وعدد' => '🔧',
            'رياضة ورحلات' => '⛺',
            'مستلزمات أطفال' => '🧸',
            'دراجات ومركبات' => '🚲',
            'آلات موسيقية' => '🎸',
            'أخرى' => '📦',
        ];

        $now = now();
        $rows = [];

        foreach ($categories as $name => $icon) {
            $rows[] = [
                'category_name' => $name,
                'icon' => $icon,
                'state' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('categories')->upsert($rows, ['category_name'], ['icon', 'state', 'updated_at']);
    }
}
