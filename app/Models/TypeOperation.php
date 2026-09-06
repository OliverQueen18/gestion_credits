<?php

namespace App\Models;

use App\Enums\OperationSens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeOperation extends Model
{
    public const CODE_CREDIT = 'CREDIT';

    public const CODE_REMBOURSEMENT = 'REMBOURSEMENT';

    protected $fillable = [
        'numero_enr',
        'code',
        'libelle',
        'sens',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'sens' => OperationSens::class,
            'actif' => 'boolean',
        ];
    }

    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class);
    }

    public function incrementeEncours(): bool
    {
        return $this->sens->incrementeEncours();
    }

    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    public static function credit(): ?self
    {
        return static::query()->where('code', self::CODE_CREDIT)->first();
    }

    public static function remboursement(): ?self
    {
        return static::query()->where('code', self::CODE_REMBOURSEMENT)->first();
    }
}
