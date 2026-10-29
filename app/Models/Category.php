<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * โมดูลสินค้า (Stock) - หมวดหมู่สินค้า
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'description', 'status'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public static function generateCode(): string
    {
        $last = static::orderByDesc('id')->first();
        $next = $last ? ((int) filter_var($last->code, FILTER_SANITIZE_NUMBER_INT)) + 1 : 1;

        return 'CAT'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
