<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * المفضلة: غرض محفوظ عند مستخدم
 */
class Wishlist extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['user_id', 'item_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
