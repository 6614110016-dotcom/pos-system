<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * โมดูลสมาชิก (Member)
 */
class Member extends Model
{
    use HasFactory;

    /** ส่วนลดตามระดับสมาชิก (เปอร์เซ็นต์) */
    public const TIER_DISCOUNT = [
        'silver'   => 0,
        'gold'     => 3,
        'platinum' => 5,
    ];

    /** ยอดซื้อสะสมที่ใช้เลื่อนระดับ (บาท) */
    public const TIER_THRESHOLD = [
        'gold'     => 5000,
        'platinum' => 20000,
    ];

    protected $fillable = [
        'member_code',
        'name',
        'phone',
        'email',
        'address',
        'birth_date',
        'gender',
        'tier',
        'points',
        'total_spent',
        'joined_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'birth_date'  => 'date',
            'joined_at'   => 'date',
            'status'      => 'boolean',
            'total_spent' => 'decimal:2',
        ];
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /* ---------------- Helpers ---------------- */

    public function getDiscountRateAttribute(): float
    {
        return (float) (self::TIER_DISCOUNT[$this->tier] ?? 0);
    }

    public function getTierNameAttribute(): string
    {
        return match ($this->tier) {
            'gold'     => 'ทอง (Gold)',
            'platinum' => 'แพลทินัม (Platinum)',
            default    => 'เงิน (Silver)',
        };
    }

    public function getTierBadgeAttribute(): string
    {
        return match ($this->tier) {
            'gold'     => 'warning',
            'platinum' => 'dark',
            default    => 'secondary',
        };
    }

    public function getGenderNameAttribute(): string
    {
        return match ($this->gender) {
            'male'   => 'ชาย',
            'female' => 'หญิง',
            'other'  => 'อื่นๆ',
            default  => '-',
        };
    }

    /**
     * ปรับระดับสมาชิกอัตโนมัติจากยอดซื้อสะสม
     */
    public function refreshTier(): void
    {
        $spent = (float) $this->total_spent;

        $tier = match (true) {
            $spent >= self::TIER_THRESHOLD['platinum'] => 'platinum',
            $spent >= self::TIER_THRESHOLD['gold']     => 'gold',
            default                                    => 'silver',
        };

        if ($tier !== $this->tier) {
            $this->update(['tier' => $tier]);
        }
    }

    /**
     * สร้างรหัสสมาชิกถัดไป เช่น MB0001
     */
    public static function generateCode(): string
    {
        $last = static::orderByDesc('id')->first();
        $next = $last ? ((int) filter_var($last->member_code, FILTER_SANITIZE_NUMBER_INT)) + 1 : 1;

        return 'MB'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('member_code', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%");
        });
    }
}
