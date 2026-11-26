<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * تقييم متبادل بعد انتهاء العملية: كل طرف بيقيم التاني مرة وحدة
 */
class Review extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['request_id', 'reviewer_id', 'reviewed_user_id', 'rating', 'comment'];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ItemRequest::class, 'request_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_user_id');
    }

    // إعادة حساب التقييم النهائي للمستخدم (متوسط كل تقييماته)
    public static function refreshUserRate(int $userId): void
    {
        $average = self::where('reviewed_user_id', $userId)->avg('rating') ?? 0;

        User::whereKey($userId)->update(['rate' => round($average, 2)]);
    }
}
