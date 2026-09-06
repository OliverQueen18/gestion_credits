<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'telephone',
        'password',
        'role',
        'is_active',
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
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'role' => UserRole::class,
        ];
    }

    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isGestionnaire(): bool
    {
        return $this->role === UserRole::Gestionnaire;
    }

    public function isConsultation(): bool
    {
        return $this->role === UserRole::Consultation;
    }

    public function canPerformFinancialOperations(): bool
    {
        return $this->is_active && $this->role->canPerformFinancialOperations();
    }

    public function canManageAdministration(): bool
    {
        return $this->is_active && $this->role->canManageAdministration();
    }

    public static function normalizeUsername(?string $username): ?string
    {
        if (! filled($username)) {
            return null;
        }

        return Str::lower(trim($username));
    }

    public static function normalizeTelephone(?string $telephone): ?string
    {
        if (! filled($telephone)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $telephone);

        return filled($digits) ? $digits : null;
    }

    public static function findForLogin(string $login): ?self
    {
        $login = trim($login);

        if ($login === '') {
            return null;
        }

        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return static::query()->where('email', Str::lower($login))->first();
        }

        $telephone = static::normalizeTelephone($login);
        if ($telephone && strlen($telephone) >= 8 && preg_match('/^[\d\s+\-().]+$/', $login)) {
            return static::query()->where('telephone', $telephone)->first();
        }

        return static::query()
            ->where('username', static::normalizeUsername($login))
            ->first();
    }
}
