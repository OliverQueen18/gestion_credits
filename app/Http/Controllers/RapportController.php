<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Operation;
use App\Models\TypeOperation;
use App\Models\User;
use App\Services\DocumentService;
use App\Services\ReportingService;
use App\Support\Money;
use App\Support\XlsxWriter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class RapportController extends Controller
{
    public function __construct(
        private readonly ReportingService $reporting,
        private readonly DocumentService $documents,
        private readonly XlsxWriter $xlsx,
    ) {}

    public function portefeuille(Request $request): View|Response
    {
        $this->authorize('viewAny', Client::class);

        $rapport = $this->reporting->rapportPortefeuille();

        if ($request->input('export') === 'excel') {
            return $this->xlsx->download('situation-portefeuille', 'Portefeuille', [
                'Indicateur', 'Valeur',
            ], [
                ['Total crédits', Money::format($rapport['total_credits'])],
                ['Total remboursements', Money::format($rapport['total_remboursements'])],
                ['Encours', Money::format($rapport['encours'])],
                ['Nombre de clients', $rapport['clients']],
                ['Nombre de débiteurs', $rapport['debiteurs']],
            ]);
        }

        if ($request->input('export') === 'pdf') {
            return $this->documents->pdf('documents.rapport-portefeuille', [
                'rapport' => $rapport,
            ], 'situation-portefeuille.pdf');
        }

        return view('rapports.portefeuille', [
            'rapport' => $rapport,
            'impression' => $request->boolean('print'),
        ]);
    }

    public function mensuel(Request $request): View|Response
    {
        $this->authorize('viewAny', Operation::class);

        $lignes = $this->reporting->rapportMensuel();

        if ($request->input('export') === 'excel') {
            return $this->xlsx->download('rapport-mensuel', 'Mensuel', [
                'Mois', 'Crédits', 'Remboursements', 'Encours',
            ], $lignes->map(fn (array $l) => [
                $l['label'],
                (float) $l['credits'],
                (float) $l['remboursements'],
                (float) $l['encours'],
            ])->all());
        }

        if ($request->input('export') === 'pdf') {
            return $this->documents->pdf('documents.rapport-mensuel', [
                'lignes' => $lignes,
            ], 'rapport-mensuel.pdf', 'a4', 'landscape');
        }

        return view('rapports.mensuel', [
            'lignes' => $lignes,
            'impression' => $request->boolean('print'),
        ]);
    }

    public function debiteurs(Request $request): View|Response
    {
        $this->authorize('viewAny', Client::class);

        $lignes = $this->reporting->rapportDebiteurs();

        if ($request->input('export') === 'excel') {
            return $this->xlsx->download('clients-debiteurs', 'Débiteurs', [
                'Code', 'Nom', 'Téléphone', 'Encours', 'Dernier crédit', 'Dernier remboursement',
            ], $lignes->map(fn (array $l) => [
                $l['code'], $l['nom'], $l['telephone'] ?? '', (float) $l['encours'], $l['dernier_credit'], $l['dernier_remboursement'],
            ])->all());
        }

        if ($request->input('export') === 'pdf') {
            return $this->documents->pdf('documents.rapport-debiteurs', [
                'lignes' => $lignes,
            ], 'clients-debiteurs.pdf', 'a4', 'landscape');
        }

        return view('rapports.debiteurs', [
            'lignes' => $lignes,
            'impression' => $request->boolean('print'),
        ]);
    }

    public function historique(Request $request): View
    {
        $this->authorize('viewAny', Operation::class);

        return view('rapports.historique', [
            'clients' => Client::query()->orderBy('nom')->get(['id', 'code_client', 'nom', 'prenom']),
            'types' => TypeOperation::query()->orderBy('id')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
