<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * โมดูลอื่นๆ - บันทึกการใช้งานระบบ (Audit Log)
 */
class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'action', 'module', 'description', 'ip_address'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, string $module, ?string $description = null): void
    {
        static::create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'module'      => $module,
            'description' => $description,
            'ip_address'  => request()->ip(),
        ]);
    }

    public function getActionBadgeAttribute(): string
    {
        return match ($this->action) {
            'create', 'sale' => 'success',
            'update'         => 'warning',
            'delete', 'void' => 'danger',
            'login'          => 'info',
            default          => 'secondary',
        };
    }
}
