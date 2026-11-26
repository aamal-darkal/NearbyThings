<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * طلب على غرض (جدول requests)
 * اسم الكلاس ItemRequest حتى ما يتضارب مع Illuminate\Http\Request
 *
 * الحالات: waiting (بانتظار رد المالك) → approved (مقبول) / rejected (مرفوض) / canceled (ألغاه الباحث)
 *          approved → received (استلم الغرض المؤجر) → finished (انتهت العملية)
 *          approved → finished (بيع أو تبادل: بعد تأكيد الاستلام)
 */
class ItemRequest extends Model
{
    protected $table = 'requests';

    const CREATED_AT = 'request_date';

    // الحالات اللي بيكون فيها الطلب "مفتوح"
    public const OPEN_STATUSES = ['waiting', 'approved', 'received'];

    protected $fillable = ['item_id', 'requester_id', 'requester_note', 'owner_note', 'count', 'status'];

    protected function casts(): array
    {
        return [
            'request_date' => 'datetime',
        ];
    }

    // ===== العلاقات =====

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function exchangeOffer(): HasOne
    {
        return $this->hasOne(ExchangeOffer::class, 'request_id');
    }

    public function rental(): HasOne
    {
        return $this->hasOne(Rental::class, 'request_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'request_id');
    }

    // آخر موعد (الموعد الحالي)
    public function appointment(): HasOne
    {
        return $this->hasOne(Appointment::class, 'request_id')->latestOfMany();
    }

    // ===== الاستعلامات =====

    // الطلبات اللي المستخدم طرف فيها (صاحب الطلب أو مالك الغرض)
    public function scopeInvolving(Builder $query, User $user): void
    {
        $query->where(fn ($q) => $q
            ->where('requester_id', $user->id)
            ->orWhereHas('item', fn ($q) => $q->where('owner_id', $user->id)));
    }

    // ===== دوال مساعدة =====

    public function ownerId(): int
    {
        return $this->item->owner_id;
    }

    public function isParty(User $user): bool
    {
        return $user->id === $this->requester_id || $user->id === $this->ownerId();
    }

    // الطرف التاني بالنسبة للمستخدم
    public function otherParty(User $user): User
    {
        return $user->id === $this->requester_id ? $this->item->owner : $this->requester;
    }

    // بيانات التواصل بتظهر للطرفين بس بعد قبول الطلب
    public function contactVisible(): bool
    {
        return in_array($this->status, ['approved', 'received', 'finished']);
    }

    public function statusLabel(): string
    {
        return [
            'waiting' => 'قيد الانتظار',
            'approved' => 'مقبول',
            'rejected' => 'مرفوض',
            'canceled' => 'ملغى',
            'received' => 'تم الاستلام',
            'finished' => 'مكتمل',
        ][$this->status];
    }

    // لون شارة الحالة
    public function statusClass(): string
    {
        return [
            'waiting' => 'status--warning',
            'approved' => 'status--success',
            'rejected' => 'status--danger',
            'canceled' => '',
            'received' => 'status--info',
            'finished' => 'status--success',
        ][$this->status];
    }
}
