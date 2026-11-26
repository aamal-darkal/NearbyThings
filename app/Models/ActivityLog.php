<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * سجل العمليات: مين عمل شو وعلى أي سجل
 */
class ActivityLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['user_id', 'action', 'table_name', 'record_id', 'old_values', 'new_values'];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // تسجيل عملية: create / update / delete
    public static function record(string $action, Model $model, ?array $old = null, ?array $new = null): void
    {
        self::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'table_name' => $model->getTable(),
            'record_id' => $model->getKey(),
            'old_values' => $old,
            'new_values' => $new,
        ]);
    }
}
