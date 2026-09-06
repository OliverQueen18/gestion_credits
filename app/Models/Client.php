<?php

namespace App\Models;

use App\Enums\ClientStatut;
use App\Support\Money;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    protected $fillable = [
        'numero_enr',
        'code_client',
        'nom',
        'prenom',
        'telephone',
        'email',
        'adresse',
        'solde',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'solde' => 'decimal:2',
            'statut' => ClientStatut::class,
        ];
    }

    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class);
    }

    public function nomComplet(): string
    {
        return trim($this->nom.' '.$this->prenom);
    }

    public function soldeFormate(): string
    {
        return Money::format($this->solde);
    }

    public function estActif(): bool
    {
        return $this->statut === ClientStatut::Actif;
    }

    public function scopeActifs($query)
    {
        return $query->where('statut', ClientStatut::Actif->value);
    }

    public function scopeAvecEncours($query)
    {
        return $query->where('solde', '>', 0);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! filled($term)) {
            return $query;
        }

        $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $term).'%';

        return $query->where(function ($q) use ($like) {
            $q->where('code_client', 'like', $like)
                ->orWhere('nom', 'like', $like)
                ->orWhere('prenom', 'like', $like)
                ->orWhere('telephone', 'like', $like)
                ->orWhere('email', 'like', $like);
        });
    }

    public static function initiales(?string $nom, ?string $prenom): string
    {
        $nom = mb_strtoupper(trim((string) $nom));
        $prenom = mb_strtoupper(trim((string) $prenom));

        $lettreNom = $nom !== '' ? mb_substr($nom, 0, 1) : 'C';
        $lettrePrenom = $prenom !== '' ? mb_substr($prenom, 0, 1) : 'L';

        return $lettreNom.$lettrePrenom;
    }

    public static function prochainNumeroSequence(): int
    {
        $max = 0;

        foreach (static::query()->pluck('code_client') as $code) {
            if (preg_match('/(\d+)\s*$/', (string) $code, $matches) === 1) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return $max + 1;
    }

    public static function proposerCode(?string $nom = null, ?string $prenom = null): string
    {
        $numero = static::prochainNumeroSequence();
        $initiales = static::initiales($nom, $prenom);

        do {
            $code = $initiales.str_pad((string) $numero, 3, '0', STR_PAD_LEFT);
            $numero++;
        } while (static::query()->where('code_client', $code)->exists());

        return $code;
    }
}
