<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // بطاقة الغرض بتحتاج تعرف إذا الغرض بمفضلة المستخدم
        // منحفظ النتيجة بالطلب الحالي حتى يصير الاستعلام مرة وحدة مهما تكررت البطاقة
        View::composer('partials.item-card', function ($view) {
            $attributes = request()->attributes;

            if (! $attributes->has('favorite_ids')) {
                $attributes->set('favorite_ids', Auth::check()
                    ? Auth::user()->favoriteItems()->pluck('items.id')->all()
                    : []);
            }

            $view->with('favoriteIds', $attributes->get('favorite_ids'));
        });
    }
}
