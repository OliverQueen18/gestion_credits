<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Operation;
use App\Support\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportingService
{
    /**
     * @return array{
     *     clients: int,
     *     clients_encours: int,
     *     total_credits: string,
     *     total_remboursements: string,
     *     encours: string,
     *     operations: int,
     *     credits_mois: string,
     *     remboursements_mois: string
     * }
     */
    public function kpis(?Carbon $mois = null): array
    {
        $mois ??= now();
        $debut = $mois->copy()->startOfMonth()->toDateString();
        $fin = $mois->copy()->endOfMonth()->toDateString();

        $totaux = Operation::query()->valides()
            ->selectRaw('COALESCE(SUM(entrees), 0) as credits, COALESCE(SUM(sorties), 0) as remboursements, COUNT(*) as nombre')
            ->first();

        $moisTotaux = Operation::query()->valides()
            ->whereDate('date_operation', '>=', $debut)
            ->whereDate('date_operation', '<=', $fin)
            ->selectRaw('COALESCE(SUM(entrees), 0) as credits, COALESCE(SUM(sorties), 0) as remboursements')
            ->first();

        $credits = $this->normalize($totaux?->credits ?? 0);
        $remboursements = $this->normalize($totaux?->remboursements ?? 0);

        return [
            'clients' => Client::query()->count(),
            'clients_encours' => Client::query()->avecEncours()->count(),
            'total_credits' => $credits,
            'total_remboursements' => $remboursements,
            'encours' => bcsub($credits, $remboursements, 2),
            'operations' => (int) ($totaux?->nombre ?? 0),
            'credits_mois' => $this->normalize($moisTotaux?->credits ?? 0),
            'remboursements_mois' => $this->normalize($moisTotaux?->remboursements ?? 0),
        ];
    }

    /**
     * @return list<array{mois: string, label: string, credits: float, remboursements: float}>
     */
    public function creditsVsRemboursementsParMois(int $nombreMois = 12): array
    {
        $debut = now()->startOfMonth()->subMonths($nombreMois - 1);
        $parMois = $this->agregerParMois($debut);

        $lignes = [];
        for ($i = 0; $i < $nombreMois; $i++) {
            $mois = $debut->copy()->addMonths($i);
            $cle = $mois->format('Y-m');
            $ligne = $parMois->get($cle);
            $lignes[] = [
                'mois' => $cle,
                'label' => $this->libelleMois($mois),
                'credits' => (float) ($ligne->credits ?? 0),
                'remboursements' => (float) ($ligne->remboursements ?? 0),
            ];
        }

        return $lignes;
    }

    /**
     * Encours cumulé à la fin de chaque mois.
     *
     * @return list<array{mois: string, label: string, encours: float}>
     */
    public function evolutionEncours(int $nombreMois = 12): array
    {
        $debut = now()->startOfMonth()->subMonths($nombreMois - 1);
        $avant = Operation::query()->valides()
            ->whereDate('date_operation', '<', $debut->toDateString())
            ->selectRaw('COALESCE(SUM(entrees), 0) as credits, COALESCE(SUM(sorties), 0) as remboursements')
            ->first();

        $encours = (float) bcsub(
            $this->normalize($avant?->credits ?? 0),
            $this->normalize($avant?->remboursements ?? 0),
            2
        );

        $parMois = $this->agregerParMois($debut);
        $lignes = [];

        for ($i = 0; $i < $nombreMois; $i++) {
            $mois = $debut->copy()->addMonths($i);
            $cle = $mois->format('Y-m');
            $ligne = $parMois->get($cle);
            $encours += (float) ($ligne->credits ?? 0) - (float) ($ligne->remboursements ?? 0);
            $lignes[] = [
                'mois' => $cle,
                'label' => $this->libelleMois($mois),
                'encours' => round($encours, 2),
            ];
        }

        return $lignes;
    }

    /**
     * @return list<array{label: string, nombre: int}>
     */
    public function repartitionEncours(): array
    {
        $soldes = Client::query()->pluck('solde');

        $tranches = [
            ['label' => 'Soldé (0)', 'min' => 0, 'max' => 0],
            ['label' => '1 à 100 000', 'min' => 0.01, 'max' => 100000],
            ['label' => '100 001 à 500 000', 'min' => 100000.01, 'max' => 500000],
            ['label' => '500 001 à 1 000 000', 'min' => 500000.01, 'max' => 1000000],
            ['label' => 'Plus de 1 000 000', 'min' => 1000000.01, 'max' => null],
        ];

        return array_map(function (array $tranche) use ($soldes) {
            $nombre = $soldes->filter(function ($solde) use ($tranche) {
                $valeur = (float) $solde;
                if ($tranche['max'] === null) {
                    return $valeur >= $tranche['min'];
                }

                return $valeur >= $tranche['min'] && $valeur <= $tranche['max'];
            })->count();

            return [
                'label' => $tranche['label'],
                'nombre' => $nombre,
            ];
        }, $tranches);
    }

    /**
     * @return array{
     *     total_credits: string,
     *     total_remboursements: string,
     *     encours: string,
     *     clients: int,
     *     debiteurs: int
     * }
     */
    public function rapportPortefeuille(): array
    {
        $kpis = $this->kpis();

        return [
            'total_credits' => $kpis['total_credits'],
            'total_remboursements' => $kpis['total_remboursements'],
            'encours' => $kpis['encours'],
            'clients' => $kpis['clients'],
            'debiteurs' => $kpis['clients_encours'],
        ];
    }

    /**
     * @return Collection<int, array{mois: string, label: string, credits: string, remboursements: string, encours: string}>
     */
    public function rapportMensuel(): Collection
    {
        $premiere = Operation::query()->valides()->min('date_operation');
        $debut = $premiere
            ? Carbon::parse($premiere)->startOfMonth()
            : now()->startOfMonth();

        $nombreMois = (int) $debut->diffInMonths(now()->startOfMonth()) + 1;
        $parMois = $this->agregerParMois($debut);
        $encours = 0.0;
        $lignes = collect();

        for ($i = 0; $i < $nombreMois; $i++) {
            $mois = $debut->copy()->addMonths($i);
            $cle = $mois->format('Y-m');
            $ligne = $parMois->get($cle);
            $credits = $this->normalize($ligne->credits ?? 0);
            $remboursements = $this->normalize($ligne->remboursements ?? 0);
            $encours = (float) bcadd(
                (string) $encours,
                bcsub($credits, $remboursements, 2),
                2
            );

            $lignes->push([
                'mois' => $cle,
                'label' => $this->libelleMois($mois, true),
                'credits' => $credits,
                'remboursements' => $remboursements,
                'encours' => $this->normalize($encours),
            ]);
        }

        return $lignes;
    }

    /**
     * @return Collection<int, array{code: string, nom: string, telephone: ?string, encours: string, dernier_credit: ?string, dernier_remboursement: ?string}>
     */
    public function rapportDebiteurs(): Collection
    {
        $creditId = DB::table('type_operations')->where('code', 'CREDIT')->value('id');
        $rembId = DB::table('type_operations')->where('code', 'REMBOURSEMENT')->value('id');

        return Client::query()
            ->avecEncours()
            ->orderByDesc('solde')
            ->orderBy('nom')
            ->get()
            ->map(function (Client $client) use ($creditId, $rembId) {
                $dernierCredit = $client->operations()
                    ->valides()
                    ->where('type_operation_id', $creditId)
                    ->orderByDesc('date_operation')
                    ->orderByDesc('id')
                    ->value('date_operation');

                $dernierRemb = $client->operations()
                    ->valides()
                    ->where('type_operation_id', $rembId)
                    ->orderByDesc('date_operation')
                    ->orderByDesc('id')
                    ->value('date_operation');

                return [
                    'id' => $client->id,
                    'code' => $client->code_client,
                    'nom' => $client->nomComplet(),
                    'telephone' => $client->telephone,
                    'encours' => $this->normalize($client->solde),
                    'encours_libelle' => Money::format($client->solde),
                    'dernier_credit' => $dernierCredit ? Carbon::parse($dernierCredit)->format('d/m/Y') : '—',
                    'dernier_remboursement' => $dernierRemb ? Carbon::parse($dernierRemb)->format('d/m/Y') : '—',
                ];
            });
    }

    /**
     * @return Collection<string, object>
     */
    private function agregerParMois(Carbon $depuis): Collection
    {
        $expr = $this->expressionMois();

        return Operation::query()->valides()
            ->whereDate('date_operation', '>=', $depuis->toDateString())
            ->selectRaw("{$expr} as mois, COALESCE(SUM(entrees), 0) as credits, COALESCE(SUM(sorties), 0) as remboursements")
            ->groupBy('mois')
            ->orderBy('mois')
            ->get()
            ->keyBy('mois');
    }

    private function expressionMois(): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', date_operation)"
            : "DATE_FORMAT(date_operation, '%Y-%m')";
    }

    private function libelleMois(Carbon $mois, bool $complet = false): string
    {
        $courts = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
        $longs = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
        $noms = $complet ? $longs : $courts;

        return $noms[$mois->month - 1].' '.$mois->year;
    }

    private function normalize(int|float|string|null $montant): string
    {
        return number_format((float) $montant, 2, '.', '');
    }
}
