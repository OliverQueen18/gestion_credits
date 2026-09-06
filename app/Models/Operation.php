<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Operation extends Model
{
    protected $fillable = [
        'numero_enr',
        'client_id',
        'type_operation_id',
        'user_id',
        'reference',
        'date_operation',
        'heure_operation',
        'montant',
        'solde_avant',
        'solde_apres',
        'observation',
        'entrees',
        'sorties',
        'mode_paiement',
        'est_annulee',
    ];

    protected function casts(): array
    {
        return [
            'date_operation' => 'date',
            'montant' => 'decimal:2',
            'solde_avant' => 'decimal:2',
            'solde_apres' => 'decimal:2',
            'entrees' => 'decimal:2',
            'sorties' => 'decimal:2',
            'est_annulee' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function typeOperation(): BelongsTo
    {
        return $this->belongsTo(TypeOperation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function corrections(): HasMany
    {
        return $this->hasMany(OperationCorrection::class, 'operation_id');
    }

    public function montantFormate(): string
    {
        return Money::format($this->montant);
    }

    public function scopeValides($query)
    {
        return $query->where('est_annulee', false);
    }

    public function scopeFiltered($query, $request)
    {
        return $query
            ->when($request->filled('client_id'), fn ($q) => $q->where('client_id', $request->integer('client_id')))
            ->when($request->filled('type'), fn ($q) => $q->where('type_operation_id', $request->integer('type')))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('reference'), fn ($q) => $q->where('reference', 'like', '%'.$request->string('reference').'%'))
            ->when($request->filled('du'), fn ($q) => $q->whereDate('date_operation', '>=', $request->input('du')))
            ->when($request->filled('au'), fn ($q) => $q->whereDate('date_operation', '<=', $request->input('au')))
            ->when($request->filled('montant_min'), fn ($q) => $q->where('montant', '>=', $request->input('montant_min')))
            ->when($request->filled('montant_max'), fn ($q) => $q->where('montant', '<=', $request->input('montant_max')));
    }

    public function estCredit(): bool
    {
        return $this->typeOperation?->incrementeEncours() ?? false;
    }

    public function estCorrigee(): bool
    {
        return $this->corrections()->exists();
    }
}
