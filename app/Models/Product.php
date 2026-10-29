<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * โมดูลสินค้า (Stock)
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_code',
        'barcode',
        'name',
        'category_id',
        'description',
        'unit',
        'cost_price',
        'price',
        'stock_qty',
        'reorder_level',
        'image',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'price'      => 'decimal:2',
            'status'     => 'boolean',
        ];
    }

    /* ---------------- Relationships ---------------- */

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /* ---------------- Helpers ---------------- */

    public function getImageUrlAttribute(): string
    {
        return $this->image && file_exists(public_path('uploads/products/'.$this->image))
            ? asset('uploads/products/'.$this->image)
            : asset('images/no-image.svg');
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_qty <= $this->reorder_level;
    }

    public function getIsOutOfStockAttribute(): bool
    {
        return $this->stock_qty <= 0;
    }

    public function getProfitPerUnitAttribute(): float
    {
        return (float) $this->price - (float) $this->cost_price;
    }

    public function getStockBadgeAttribute(): string
    {
        return match (true) {
            $this->stock_qty <= 0                 => 'danger',
            $this->stock_qty <= $this->reorder_level => 'warning',
            default                               => 'success',
        };
    }

    /**
     * บันทึกการเคลื่อนไหวสต็อกพร้อมอัปเดตยอดคงเหลือ
     */
    public function adjustStock(int $qty, string $type, ?string $reference = null, ?string $note = null, ?int $userId = null): StockMovement
    {
        $before = $this->stock_qty;
        $this->stock_qty = $before + $qty;
        $this->save();

        return $this->stockMovements()->create([
            'type'       => $type,
            'qty'        => $qty,
            'qty_before' => $before,
            'qty_after'  => $this->stock_qty,
            'cost_price' => $this->cost_price,
            'reference'  => $reference,
            'note'       => $note,
            'user_id'    => $userId ?? auth()->id(),
        ]);
    }

    public static function generateCode(): string
    {
        $last = static::orderByDesc('id')->first();
        $next = $last ? ((int) filter_var($last->product_code, FILTER_SANITIZE_NUMBER_INT)) + 1 : 1;

        return 'P'.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    /* ---------------- Scopes ---------------- */

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_qty', '<=', 'reorder_level');
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('product_code', 'like', "%{$term}%")
              ->orWhere('barcode', 'like', "%{$term}%");
        });
    }
}
