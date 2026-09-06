<?php

namespace App\Console\Commands;

use App\Services\PortefeuilleService;
use App\Support\Money;
use Illuminate\Console\Command;

class RecalculateClientBalancesCommand extends Command
{
    protected $signature = 'clients:recalculate-balances {--fix : Corriger les soldes incohérents}';

    protected $description = 'Recalcule les soldes clients depuis OPERATIONS et signale les écarts';

    public function handle(PortefeuilleService $portefeuille): int
    {
        $corriger = (bool) $this->option('fix');
        $resultats = $portefeuille->recalculerTousLesSoldes($corriger);
        $ecarts = $resultats->reject(fn (array $r) => $r['coherent'] && empty($r['corrige']));

        $this->info($resultats->count().' client(s) analysé(s).');

        if ($ecarts->isEmpty()) {
            $this->info('Tous les soldes sont cohérents avec le journal des opérations.');

            return self::SUCCESS;
        }

        $this->table(
            ['Code', 'Client', 'Solde enregistré', 'Solde calculé', 'État'],
            $ecarts->map(fn (array $r) => [
                $r['client']->code_client,
                $r['client']->nomComplet(),
                Money::format($r['solde_enregistre']),
                Money::format($r['solde_calcule']),
                isset($r['corrige']) ? 'Corrigé' : 'Écart',
            ])->all()
        );

        if (! $corriger) {
            $this->warn('Relancer avec --fix pour aligner le champ solde sur le journal.');
        }

        return self::SUCCESS;
    }
}
