<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * โมดูลการขาย (Sale) - หัวบิล
 */
class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_no',
        'member_id',
        'user_id',
        'subtotal',
        'discount',
        'member_discount',
        'vat',
        'total',
        'points_earned',
        'status',
        'note',
        'sold_at',
        'voided_by',
        'voided_at',
        'void_reason',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'        => 'decimal:2',
            'discount'        => 'decimal:2',
            'member_discount' => 'decimal:2',
            'vat'             => 'decimal:2',
            'total'           => 'decimal:2',
            'sold_at'         => 'datetime',
            'voided_at'       => 'datetime',
        ];
    }

    /* ---------------- Relationships ---------------- */

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /* ---------------- Helpers ---------------- */

    public function getPaidAmountAttribute(): float
    {
        return (float) $this->payments()->where('status', 'success')->sum('amount');
    }

    public function getOutstandingAttribute(): float
    {
        return round((float) $this->total - $this->paid_amount, 2);
    }

    public function getTotalQtyAttribute(): int
    {
        return (int) $this->items()->sum('qty');
    }

    public function getProfitAttribute(): float
    {
        return (float) $this->items->sum(fn ($i) => ($i->price - $i->cost_price) * $i->qty - $i->discount);
    }

    public function getStatusNameAttribute(): string
    {
        return match ($this->status) {
            'paid'  => 'ชำระแล้ว',
            'void'  => 'ยกเลิก',
            default => 'รอชำระเงิน',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'paid'  => 'success',
            'void'  => 'danger',
            default => 'warning',
        };
    }

    public function getCustomerNameAttribute(): string
    {
        return $this->member?->name ?? 'ลูกค้าทั่วไป';
    }

    /**
     * สร้างเลขที่บิล เช่น INV-20260904-0001
     */
    public static function generateSaleNo(): string
    {
        $prefix = 'INV-'.now()->format('Ymd').'-';
        $count  = static::where('sale_no', 'like', $prefix.'%')->count() + 1;

        return $prefix.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    /* ---------------- Scopes ---------------- */

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('sold_at', today());
    }

    public function scopeBetween($query, $from, $to)
    {
        return $query->whereBetween('sold_at', [
            $from.' 00:00:00',
            $to.' 23:59:59',
        ]);
    }
}
