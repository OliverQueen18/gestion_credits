<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Operation;
use App\Models\OperationCorrection;
use App\Models\Setting;
use App\Models\TypeOperation;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PortefeuilleService
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function createCredit(Client $client, array $data, User $user): Operation
    {
        $type = $this->typeActif(TypeOperation::CODE_CREDIT);

        return $this->enregistrer($client, $type, $data, $user, 'creation_credit');
    }

    public function createRemboursement(Client $client, array $data, User $user): Operation
    {
        $type = $this->typeActif(TypeOperation::CODE_REMBOURSEMENT);

        return $this->enregistrer($client, $type, $data, $user, 'creation_remboursement');
    }

    public function soldeCalcule(Client $client): string
    {
        $totaux = $this->totaux($client);

        return bcsub($totaux['credits'], $totaux['remboursements'], 2);
    }

    /**
     * @return array{credits: string, remboursements: string}
     */
    public function totaux(Client $client): array
    {
        $aggregats = $client->operations()
            ->valides()
            ->selectRaw('COALESCE(SUM(entrees), 0) as credits, COALESCE(SUM(sorties), 0) as remboursements')
            ->first();

        return [
            'credits' => $this->normalizeMontant($aggregats?->credits ?? 0),
            'remboursements' => $this->normalizeMontant($aggregats?->remboursements ?? 0),
        ];
    }

    /**
     * @return array{client: Client, solde_enregistre: string, solde_calcule: string, coherent: bool}
     */
    public function verifierSolde(Client $client): array
    {
        $calcule = $this->soldeCalcule($client);
        $enregistre = $this->normalizeMontant($client->solde);

        return [
            'client' => $client,
            'solde_enregistre' => $enregistre,
            'solde_calcule' => $calcule,
            'coherent' => bccomp($enregistre, $calcule, 2) === 0,
        ];
    }

    /**
     * @return Collection<int, array{client: Client, solde_enregistre: string, solde_calcule: string, coherent: bool}>
     */
    public function recalculerTousLesSoldes(bool $corriger = false): Collection
    {
        return Client::query()->orderBy('id')->get()->map(function (Client $client) use ($corriger) {
            $resultat = $this->verifierSolde($client);

            if ($corriger && ! $resultat['coherent']) {
                $client->update(['solde' => $resultat['solde_calcule']]);
                $resultat['solde_enregistre'] = $resultat['solde_calcule'];
                $resultat['coherent'] = true;
                $resultat['corrige'] = true;
            }

            return $resultat;
        });
    }

    public function correcter(Operation $operation, string $motif, User $user): Operation
    {
        if ($operation->est_annulee || $operation->estCorrigee()) {
            throw ValidationException::withMessages([
                'operation' => 'Cette opération a déjà été corrigée.',
            ]);
        }

        $derniereId = Operation::query()
            ->where('client_id', $operation->client_id)
            ->valides()
            ->orderByDesc('id')
            ->value('id');

        if ((int) $derniereId !== (int) $operation->id) {
            throw ValidationException::withMessages([
                'operation' => 'Seule la dernière opération du client peut être corrigée, afin de préserver l’historique.',
            ]);
        }

        $operation->loadMissing(['client', 'typeOperation']);
        $client = $operation->client;
        $donnees = [
            'montant' => $operation->montant,
            'date_operation' => now()->toDateString(),
            'observation' => 'Correction de '.$operation->reference.' — '.$motif,
            'mode_paiement' => $operation->mode_paiement,
        ];

        $correction = $operation->estCredit()
            ? $this->createRemboursement($client, $donnees, $user)
            : $this->createCredit($client, $donnees, $user);

        OperationCorrection::query()->create([
            'operation_id' => $operation->id,
            'operation_correction_id' => $correction->id,
            'user_id' => $user->id,
            'motif' => $motif,
        ]);

        $this->audit->log('correction', $operation, [
            'reference' => $operation->reference,
        ], [
            'reference_correction' => $correction->reference,
            'motif' => $motif,
        ]);

        return $correction;
    }

    public function generateReference(TypeOperation $type, Carbon $date): string
    {
        $prefix = $type->incrementeEncours()
            ? (string) Setting::getValue('credit_reference_prefix', config('portefeuille.credit_reference_prefix'))
            : (string) Setting::getValue('repayment_reference_prefix', config('portefeuille.repayment_reference_prefix'));

        $datePart = $date->format('Ymd');
        $pattern = $prefix.'-'.$datePart.'-';

        $dernier = Operation::query()
            ->where('reference', 'like', $pattern.'%')
            ->orderByDesc('reference')
            ->lockForUpdate()
            ->value('reference');

        $sequence = $dernier ? ((int) substr($dernier, -6)) + 1 : 1;

        return $pattern.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    private function enregistrer(Client $client, TypeOperation $type, array $data, User $user, string $auditAction): Operation
    {
        if (! $client->estActif()) {
            throw ValidationException::withMessages([
                'client_id' => 'Impossible d’enregistrer une opération sur un client inactif.',
            ]);
        }

        $montant = $this->normalizeMontant($data['montant'] ?? 0);

        if (bccomp($montant, '0', 2) !== 1) {
            throw ValidationException::withMessages([
                'montant' => 'Le montant doit être supérieur à zéro.',
            ]);
        }

        $tentatives = 0;

        while ($tentatives < 3) {
            try {
                return $this->dansTransaction(function () use ($client, $type, $data, $user, $montant, $auditAction) {
                    /** @var Client $client */
                    $client = Client::query()->whereKey($client->id)->lockForUpdate()->firstOrFail();

                    $soldeAvant = $this->normalizeMontant($client->solde);
                    $augmente = $type->incrementeEncours();

                    if (! $augmente && bccomp($montant, $soldeAvant, 2) === 1 && ! Setting::allowsNegativeBalance()) {
                        throw ValidationException::withMessages([
                            'montant' => 'Le montant du remboursement ne peut pas être supérieur à l’encours du client.',
                        ]);
                    }

                    $soldeApres = $augmente
                        ? bcadd($soldeAvant, $montant, 2)
                        : bcsub($soldeAvant, $montant, 2);

                    $date = Carbon::parse($data['date_operation'] ?? now()->toDateString());

                    $operation = Operation::query()->create([
                        'client_id' => $client->id,
                        'type_operation_id' => $type->id,
                        'user_id' => $user->id,
                        'reference' => $this->generateReference($type, $date),
                        'date_operation' => $date->toDateString(),
                        'heure_operation' => now()->format('H:i:s'),
                        'montant' => $montant,
                        'solde_avant' => $soldeAvant,
                        'solde_apres' => $soldeApres,
                        'observation' => $data['observation'] ?? null,
                        'entrees' => $augmente ? $montant : '0.00',
                        'sorties' => $augmente ? '0.00' : $montant,
                        'mode_paiement' => $data['mode_paiement'] ?? null,
                        'est_annulee' => false,
                    ]);

                    $client->update(['solde' => $soldeApres]);

                    $this->audit->log($auditAction, $operation, null, [
                        'reference' => $operation->reference,
                        'montant' => $montant,
                        'solde_avant' => $soldeAvant,
                        'solde_apres' => $soldeApres,
                    ]);

                    return $operation;
                });
            } catch (UniqueConstraintViolationException $e) {
                $tentatives++;
                if ($tentatives >= 3) {
                    throw $e;
                }
            }
        }

        throw new \RuntimeException('Impossible de générer une référence unique.');
    }

    /**
     * MySQL : transaction + lockForUpdate.
     * SQLite : BEGIN IMMEDIATE, car FOR UPDATE n’existe pas.
     */
    private function dansTransaction(callable $callback): mixed
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return DB::transaction($callback);
        }

        if (DB::transactionLevel() > 0) {
            return $callback();
        }

        $pdo = DB::connection()->getPdo();
        $essais = 0;

        while ($essais < 8) {
            $ouverte = false;
            try {
                $pdo->exec('BEGIN IMMEDIATE');
                $ouverte = true;
                $resultat = $callback();
                $pdo->exec('COMMIT');

                return $resultat;
            } catch (\Throwable $e) {
                if ($ouverte) {
                    try {
                        $pdo->exec('ROLLBACK');
                    } catch (\Throwable) {
                    }
                }

                $verrouille = str_contains($e->getMessage(), 'database is locked')
                    || str_contains($e->getMessage(), 'HY000');

                if ($verrouille && ++$essais < 8) {
                    usleep(40_000 * $essais);
                    continue;
                }

                throw $e;
            }
        }

        throw new \RuntimeException('Impossible d’obtenir le verrou SQLite.');
    }

    private function typeActif(string $code): TypeOperation
    {
        $type = TypeOperation::query()->where('code', $code)->first();

        if (! $type || ! $type->actif) {
            throw ValidationException::withMessages([
                'type_operation_id' => "Le type d’opération {$code} est introuvable ou inactif.",
            ]);
        }

        return $type;
    }

    public function normalizeMontant(int|float|string|null $montant): string
    {
        if (is_string($montant)) {
            $montant = str_replace([' ', "\u{00A0}", ','], ['', '', '.'], $montant);
        }

        return number_format((float) $montant, 2, '.', '');
    }
}
