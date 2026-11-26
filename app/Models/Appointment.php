<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * موعد الاستلام: بيقترحه طرف وبيأكده الطرف التاني
 * الحالة: new (مقترح) / confirmed (مؤكد) / canceled (انلغى أو انقترح غيره)
 */
class Appointment extends Model
{
    protected $fillable = ['request_id', 'proposed_by', 'meeting_date', 'meeting_location', 'status'];

    protected function casts(): array
    {
        return [
            'meeting_date' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ItemRequest::class, 'request_id');
    }

    public function proposer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }

    // هل المستخدم هو اللي لازم يأكد هالموعد
    // (اذا ما في مقترح محفوظ، منعتبره المالك لأنه بيحدد الموعد عند القبول)
    public function awaitsConfirmationFrom(User $user): bool
    {
        $proposerId = $this->proposed_by ?? $this->request->item->owner_id;

        return $this->status === 'new' && $proposerId !== $user->id;
    }

    public function statusLabel(): string
    {
        return ['new' => 'بانتظار التأكيد', 'confirmed' => 'موعد مؤكد', 'canceled' => 'ملغى'][$this->status];
    }
}
