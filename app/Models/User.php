<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * โมดูลพนักงาน (Employee) / โมดูลยืนยันตัวตน (Login)
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN   = 'admin';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_CASHIER = 'cashier';

    protected $fillable = [
        'employee_code',
        'name',
        'email',
        'password',
        'phone',
        'address',
        'role',
        'position',
        'salary',
        'hired_at',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'hired_at'          => 'date',
            'last_login_at'     => 'datetime',
            'status'            => 'boolean',
            'salary'            => 'decimal:2',
        ];
    }

    /* ---------------- Relationships ---------------- */

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /* ---------------- Helpers ---------------- */

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function getRoleNameAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN   => 'ผู้ดูแลระบบ',
            self::ROLE_MANAGER => 'ผู้จัดการร้าน',
            default            => 'พนักงานขาย',
        };
    }

    public function getRoleBadgeAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN   => 'danger',
            self::ROLE_MANAGER => 'warning',
            default            => 'primary',
        };
    }

    /**
     * สร้างรหัสพนักงานถัดไป เช่น EMP0001
     */
    public static function generateCode(): string
    {
        $last = static::orderByDesc('id')->first();
        $next = $last ? ((int) filter_var($last->employee_code, FILTER_SANITIZE_NUMBER_INT)) + 1 : 1;

        return 'EMP'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
