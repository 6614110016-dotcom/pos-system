<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * โมดูลอื่นๆ - ตั้งค่าระบบ
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'label'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever('setting.'.$key, function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    public static function put(string $key, mixed $value, string $group = 'general', ?string $label = null): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'label' => $label]
        );

        Cache::forget('setting.'.$key);
    }

    public static function vatRate(): float
    {
        return (float) static::get('vat_rate', config('app.shop.vat_rate', 7));
    }

    /** ยอดซื้อกี่บาทได้ 1 แต้ม */
    public static function pointRate(): float
    {
        return (float) static::get('point_rate', 100);
    }
}
