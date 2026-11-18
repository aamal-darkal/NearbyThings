<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Reference data: item categories. Safe to run multiple times.
     */
    public function run(): void
    {
        $categories = [
            'إلكترونيات',
            'هواتف وأجهزة لوحية',
            'أجهزة منزلية',
            'أثاث',
            'ملابس وأحذية',
            'كتب وقرطاسية',
            'أدوات وعدد',
            'رياضة ورحلات',
            'مستلزمات أطفال',
            'دراجات ومركبات',
            'آلات موسيقية',
            'أخرى',
        ];

        $now = now();

        DB::table('categories')->upsert(
            array_map(fn (string $name) => [
                'category_name' => $name,
                'state' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ], $categories),
            ['category_name'],
            ['state', 'updated_at'],
        );
    }
}
