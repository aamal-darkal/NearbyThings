<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * إشعار داخل الموقع لمستخدم
 * (جدول خاص بالمشروع، مو إشعارات Laravel المدمجة)
 */
class Notification extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['user_id', 'title', 'message', 'link', 'is_read'];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // إرسال إشعار لمستخدم (مع رابط اختياري للصفحة المرتبطة)
    public static function send(int $userId, string $title, string $message, ?string $link = null): self
    {
        return self::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'link' => $link,
        ]);
    }
}
