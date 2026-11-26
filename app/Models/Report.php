<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * بلاغ عن مخالفة: على غرض أو على طلب (عملية)
 * الحالة: pending (قيد المراجعة) / resolved (تم اتخاذ إجراء) / rejected (مرفوض)
 */
class Report extends Model
{
    const CREATED_AT = 'reported_at';

    // أنواع المخالفات
    public const CATEGORIES = [
        'fraud' => 'خداع في التعامل',
        'prohibited' => 'غرض غير مناسب أو محظور',
        'other' => 'سبب آخر',
    ];

    protected $fillable = ['reporter_id', 'item_id', 'request_id', 'category', 'reason', 'status', 'admin_note'];

    protected function casts(): array
    {
        return [
            'reported_at' => 'datetime',
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ItemRequest::class, 'request_id');
    }

    // الغرض المرتبط (مباشرة أو عن طريق الطلب)
    public function relatedItem(): ?Item
    {
        return $this->item ?? $this->request?->item;
    }

    // الطرف المُبلَّغ عنه: مالك الغرض، أو الطرف التاني بالطلب
    public function reportedUser(): ?User
    {
        if ($this->request) {
            return $this->request->requester_id === $this->reporter_id
                ? $this->request->item->owner
                : $this->request->requester;
        }

        return $this->item?->owner;
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? self::CATEGORIES['other'];
    }

    public function statusLabel(): string
    {
        return ['pending' => 'جديد', 'resolved' => 'تم الحل', 'rejected' => 'مرفوض'][$this->status] ?? $this->status;
    }

    public function statusClass(): string
    {
        return ['pending' => 'status--warning', 'resolved' => 'status--success', 'rejected' => ''][$this->status] ?? '';
    }
}
