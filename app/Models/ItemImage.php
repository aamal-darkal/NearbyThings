<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * صورة غرض، محفوظة بمجلد public/uploads
 */
class ItemImage extends Model
{
    public $timestamps = false;

    protected $fillable = ['item_id', 'image_path'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    // رابط الصورة للعرض
    public function url(): string
    {
        return asset('uploads/'.$this->image_path);
    }
}
