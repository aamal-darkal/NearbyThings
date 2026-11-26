<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * المقابل المعروض بطلب تبادل: غرض من أغراض الباحث، أو وصف نصي
 */
class ExchangeOffer extends Model
{
    public $timestamps = false;

    protected $fillable = ['request_id', 'offered_item_id', 'offered_description'];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ItemRequest::class, 'request_id');
    }

    public function offeredItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'offered_item_id');
    }

    // نص المقابل للعرض
    public function label(): string
    {
        return $this->offeredItem?->title ?? $this->offered_description ?? '—';
    }
}
