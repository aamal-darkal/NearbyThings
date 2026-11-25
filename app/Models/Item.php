<?php

namespace App\Models;

use App\Support\Geo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * الغرض المعروض (إعلان): بيع / تبادل / تأجير
 * الحالة: active / inactive (موقوف مؤقتاً) / banned (محجوب من المشرف) / sold / rented
 */
class Item extends Model
{
    protected $fillable = [
        'owner_id', 'category_id', 'condition_id', 'transaction_type', 'title', 'description',
        'price', 'count', 'state', 'location_hint', 'latitude', 'longitude', 'variants',
        'max_rent_duration', 'rent_unit',
    ];

    // المسافة عن المستخدم بالكيلومتر (تنحسب وقت البحث، مو عمود بالجدول)
    public ?float $distance = null;

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'latitude' => 'float',
            'longitude' => 'float',
            'variants' => 'array',
        ];
    }

    // ===== العلاقات =====

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function condition(): BelongsTo
    {
        return $this->belongsTo(Condition::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ItemImage::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wishlists');
    }

    // ===== الاستعلامات =====

    // الأغراض الظاهرة للناس: نشطة، وصاحبها مو محظور
    public function scopeVisible(Builder $query): void
    {
        $query->where('state', 'active')
            ->whereHas('owner', fn ($q) => $q->where('is_blocked', false));
    }

    // ===== النصوص للعرض =====

    public function typeLabel(): string
    {
        return ['sale' => 'بيع', 'exchange' => 'تبادل', 'rent' => 'تأجير'][$this->transaction_type];
    }

    public function stateLabel(): string
    {
        return [
            'active' => 'نشط ومتاح',
            'inactive' => 'موقوف مؤقتاً',
            'banned' => 'محجوب',
            'sold' => 'صفقة مكتملة',
            'rented' => 'مؤجر حالياً',
        ][$this->state];
    }

    public function rentUnitLabel(): string
    {
        return ['day' => 'يوم', 'month' => 'شهر', 'year' => 'سنة'][$this->rent_unit ?? 'day'];
    }

    // السعر بدون الوحدة: "7,000" أو "متاح للتبادل"
    public function priceNumber(): string
    {
        if ($this->transaction_type === 'exchange') {
            return 'متاح للتبادل';
        }

        return number_format($this->price);
    }

    // السعر كامل: "7,000 ل.س" أو "350 ل.س / يوم"
    public function priceLabel(): string
    {
        if ($this->transaction_type === 'exchange') {
            return 'متاح للتبادل';
        }

        $label = number_format($this->price).' ل.س';

        if ($this->transaction_type === 'rent') {
            $label .= ' / '.$this->rentUnitLabel();
        }

        return $label;
    }

    // رابط الصورة الرئيسية (أول صورة) أو null
    public function mainImageUrl(): ?string
    {
        $image = $this->images->first();

        return $image ? $image->url() : null;
    }

    public function distanceLabel(): string
    {
        return Geo::label($this->distance);
    }

    // اسم المنطقة التقريبي
    public function areaLabel(): string
    {
        return $this->location_hint ?: ($this->owner->address ?? '');
    }

    // هل الغرض مرتبط بطلبات مفتوحة (بانتظار الرد، مقبولة، أو مستلمة وما رجعت)
    public function hasOpenRequests(): bool
    {
        return DB::table('requests')
            ->where('item_id', $this->id)
            ->whereIn('status', ['waiting', 'approved', 'received'])
            ->exists();
    }

    // هل الغرض عليه أي طلب (حتى المنتهية) - وقتها ما منحذفه لنحافظ على السجل
    public function hasAnyRequests(): bool
    {
        return DB::table('requests')->where('item_id', $this->id)->exists();
    }
}
