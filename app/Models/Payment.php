<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * โมดูลจ่ายเงิน (Payment)
 */
class Payment extends Model
{
    use HasFactory;

    public const METHODS = [
        'cash'        => 'เงินสด',
        'transfer'    => 'โอนเงินผ่านธนาคาร',
        'promptpay'   => 'พร้อมเพย์ (QR)',
        'credit_card' => 'บัตรเครดิต/เดบิต',
        'e_wallet'    => 'กระเป๋าเงินอิเล็กทรอนิกส์',
    ];

    protected $fillable = [
        'payment_no',
        'sale_id',
        'method',
        'amount',
        'received',
        'change_amount',
        'reference_no',
        'slip_image',
        'status',
        'user_id',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'        => 'decimal:2',
            'received'      => 'decimal:2',
            'change_amount' => 'decimal:2',
            'paid_at'       => 'datetime',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getMethodNameAttribute(): string
    {
        return self::METHODS[$this->method] ?? $this->method;
    }

    public function getMethodIconAttribute(): string
    {
        return match ($this->method) {
            'cash'        => 'bi-cash-stack',
            'transfer'    => 'bi-bank',
            'promptpay'   => 'bi-qr-code',
            'credit_card' => 'bi-credit-card',
            default       => 'bi-wallet2',
        };
    }

    public static function generatePaymentNo(): string
    {
        $prefix = 'PAY-'.now()->format('Ymd').'-';
        $count  = static::where('payment_no', 'like', $prefix.'%')->count() + 1;

        return $prefix.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }
}
