<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * تفاصيل التأجير لطلب (طلب واحد = سجل واحد)
 * حالة الإرجاع: pending → confirmed (أكد المالك) / disputed (اعترض) / auto_confirmed (ما رد خلال 48 ساعة)
 */
class Rental extends Model
{
    // مدة رد المالك على الإرجاع (بالساعات)
    public const RETURN_CONFIRM_HOURS = 48;

    protected $fillable = [
        'request_id', 'start_date', 'expected_return_date', 'actual_return_date', 'return_status', 'return_reported_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'expected_return_date' => 'date',
            'actual_return_date' => 'date',
            'return_reported_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ItemRequest::class, 'request_id');
    }

    // عدد أيام التأجير (مع يوم البداية)
    public function days(): int
    {
        return (int) $this->start_date->diffInDays($this->expected_return_date) + 1;
    }

    // هل الباحث بلّغ إنه رجّع الغرض والمالك لسا ما رد
    public function awaitingOwner(): bool
    {
        return $this->return_reported_at !== null && $this->return_status === 'pending';
    }

    // آخر موعد لرد المالك
    public function confirmDeadline(): ?\Illuminate\Support\Carbon
    {
        return $this->return_reported_at?->copy()->addHours(self::RETURN_CONFIRM_HOURS);
    }

    public function returnStatusLabel(): string
    {
        return [
            'pending' => $this->return_reported_at ? 'بانتظار تأكيد المالك' : 'لم يُرجع بعد',
            'confirmed' => 'أكد المالك الإرجاع',
            'disputed' => 'اعترض المالك (بلاغ)',
            'auto_confirmed' => 'تأكيد تلقائي بعد 48 ساعة',
        ][$this->return_status];
    }

    /**
     * التأكيد التلقائي: كل إرجاع ما رد عليه المالك خلال 48 ساعة بيعتبر مؤكد
     * بيشتغل من المهمة المجدولة، وكمان عند فتح صفحات الطلبات
     */
    public static function autoConfirmDue(): int
    {
        $due = self::with('request.item')
            ->where('return_status', 'pending')
            ->whereNotNull('return_reported_at')
            ->where('return_reported_at', '<=', now()->subHours(self::RETURN_CONFIRM_HOURS))
            ->get();

        foreach ($due as $rental) {
            $rental->update(['return_status' => 'auto_confirmed']);
            $rental->request->update(['status' => 'finished']);

            $item = $rental->request->item;
            if ($item->state === 'rented') {
                $item->update(['state' => 'active']);
            }

            Notification::send($item->owner_id, 'تم تأكيد الإرجاع تلقائياً', 'تم اعتبار "'.$item->title.'" مُرجعاً تلقائياً لعدم الرد خلال 48 ساعة.', route('requests.show', $rental->request));
            Notification::send($rental->request->requester_id, 'تم تأكيد الإرجاع', 'تم تأكيد إرجاع "'.$item->title.'" تلقائياً. يمكنك الآن تقييم المالك.', route('requests.show', $rental->request));
        }

        return $due->count();
    }
}
