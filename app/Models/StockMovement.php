<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * โมดูลสินค้า (Stock) - ประวัติการเคลื่อนไหวสต็อก
 */
class StockMovement extends Model
{
    use HasFactory;

    public const TYPES = [
        'in'     => 'รับสินค้าเข้า',
        'out'    => 'ตัดสต็อก (ขาย)',
        'adjust' => 'ปรับยอดสต็อก',
        'return' => 'รับคืนสินค้า',
    ];

    protected $fillable = [
        'product_id',
        'type',
        'qty',
        'qty_before',
        'qty_after',
        'cost_price',
        'reference',
        'note',
        'user_id',
    ];

    protected function casts(): array
    {
        return ['cost_price' => 'decimal:2'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getTypeBadgeAttribute(): string
    {
        return match ($this->type) {
            'in'     => 'success',
            'out'    => 'danger',
            'adjust' => 'warning',
            'return' => 'info',
            default  => 'secondary',
        };
    }
}
