<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * تصنيف الأغراض
 * الحالة: active (معتمد) / new (مقترح من مستخدم وبانتظار المشرف) / banned (موقوف)
 */
class Category extends Model
{
    protected $fillable = ['category_name', 'icon', 'state'];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    // التصنيفات المعتمدة فقط (تظهر بالفلاتر)
    public function scopeActive(Builder $query): void
    {
        $query->where('state', 'active');
    }

    // الأيقونة أو رمز افتراضي
    public function iconLabel(): string
    {
        return $this->icon ?: '📦';
    }
}
