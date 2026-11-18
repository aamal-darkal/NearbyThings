<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConditionSeeder extends Seeder
{
    /**
     * Reference data: item conditions, ordered from best to worst. Safe to run multiple times.
     */
    public function run(): void
    {
        $conditions = [
            'جديد',
            'شبه جديد',
            'مستعمل - حالة ممتازة',
            'مستعمل - حالة جيدة',
            'مستعمل - حالة مقبولة',
            'يحتاج إلى صيانة',
        ];

        $now = now();

        DB::table('conditions')->upsert(
            array_map(fn (string $name, int $i) => [
                'condition_name' => $name,
                'order' => $i + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ], $conditions, array_keys($conditions)),
            ['condition_name'],
            ['order', 'updated_at'],
        );
    }
}
