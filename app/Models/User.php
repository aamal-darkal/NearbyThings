<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['full_name', 'email', 'password', 'phone', 'address', 'city', 'latitude', 'longitude'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements HasLocalePreference
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_blocked' => 'boolean',
            'is_admin' => 'boolean',
            'rate' => 'float',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    /**
     * لغة الإيميلات والإشعارات اللي بتنبعت للمستخدم
     */
    public function preferredLocale(): string
    {
        return 'ar';
    }

    // ===== العلاقات =====

    // الأغراض اللي عرضها المستخدم
    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'owner_id');
    }

    // الأغراض المحفوظة بالمفضلة
    public function favoriteItems(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'wishlists')->withPivot('created_at');
    }

    // إشعارات الموقع (اسمها مختلف عن notifications تبع Laravel)
    public function siteNotifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    // ===== دوال مساعدة للعرض =====

    // أول حرف من الاسم (للصورة الرمزية)
    public function initial(): string
    {
        return Str::substr(trim($this->full_name), 0, 1);
    }

    // الاسم المختصر: "أحمد خ."
    public function shortName(): string
    {
        $parts = preg_split('/\s+/u', trim($this->full_name));

        if (count($parts) < 2) {
            return $parts[0];
        }

        return $parts[0].' '.Str::substr($parts[1], 0, 1).'.';
    }

    // المنطقة التقريبية: "المزة، دمشق"
    public function areaLabel(): string
    {
        return collect([$this->address, $this->city])->filter()->implode('، ');
    }

    public function hasLocation(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function unreadNotificationsCount(): int
    {
        return $this->siteNotifications()->where('is_read', false)->count();
    }
}
