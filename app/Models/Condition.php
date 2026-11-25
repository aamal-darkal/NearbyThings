<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * حالة الغرض (جديد، شبه جديد، مستعمل...) مرتبة بعمود order
 */
class Condition extends Model
{
    protected $fillable = ['condition_name', 'order'];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
