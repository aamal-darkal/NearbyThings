<?php

use App\Models\Rental;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// التأكيد التلقائي للإرجاع: كل إرجاع ما رد عليه المالك خلال 48 ساعة بيعتبر مؤكد
// التشغيل اليدوي: php artisan rentals:auto-confirm
Artisan::command('rentals:auto-confirm', function () {
    $count = Rental::autoConfirmDue();
    $this->info("تم تأكيد {$count} عملية إرجاع تلقائياً");
})->purpose('Auto-confirm rental returns after 48 hours without an owner reply');

// المهمة المجدولة: كل ساعة (بتحتاج تشغيل php artisan schedule:work)
Schedule::command('rentals:auto-confirm')->hourly();
