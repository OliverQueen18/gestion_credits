<?php

namespace App\Http\Controllers;

use App\Http\Requests\CorrectOperationRequest;
use App\Http\Requests\StoreCreditRequest;
use App\Http\Requests\StoreRemboursementRequest;
use App\Models\Client;
use App\Models\Operation;
use App\Models\TypeOperation;
use App\Models\User;
use App\Services\DocumentService;
use App\Services\PortefeuilleService;
use App\Support\XlsxWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class OperationController extends Controller
{
    public function __construct(
        private readonly PortefeuilleService $portefeuille,
        private readonly DocumentService $documents,
        private readonly XlsxWriter $xlsx,
    ) {}

    public function index(Request $request): View|Response
    {
        $this->authorize('viewAny', Operation::class);

        return $this->liste($request, 'Opérations');
    }

    public function credits(Request $request): View|Response
    {
        $this->authorize('viewAny', Operation::class);

        $type = TypeOperation::credit();
        $request->merge(['type' => $type?->id]);

        return $this->liste($request, 'Crédits', $type?->id);
    }

    public function remboursements(Request $request): View|Response
    {
        $this->authorize('viewAny', Operation::class);

        $type = TypeOperation::remboursement();
        $request->merge(['type' => $type?->id]);

        return $this->liste($request, 'Remboursements', $type?->id);
    }

    public function show(Operation $operation): View
    {
        $this->authorize('view', $operation);
        $operation->load(['client', 'typeOperation', 'user', 'corrections.user', 'corrections.operationCorrection']);

        return view('operations.show', compact('operation'));
    }

    public function createCredit(Request $request): View
    {
        $this->authorize('create', Operation::class);

        return view('operations.credit', [
            'clients' => Client::query()->actifs()->orderBy('nom')->get(),
            'client' => $request->filled('client') ? Client::query()->find($request->integer('client')) : null,
        ]);
    }

    public function storeCredit(StoreCreditRequest $request): RedirectResponse
    {
        $client = Client::query()->findOrFail($request->integer('client_id'));
        $operation = $this->portefeuille->createCredit($client, $request->validated(), $request->user());

        return redirect()
            ->route('operations.show', $operation)
            ->with('success', 'Crédit '.$operation->reference.' enregistré.');
    }

    public function createRemboursement(Request $request): View
    {
        $this->authorize('create', Operation::class);

        return view('operations.remboursement', [
            'clients' => Client::query()->actifs()->orderBy('nom')->get(),
            'client' => $request->filled('client') ? Client::query()->find($request->integer('client')) : null,
        ]);
    }

    public function storeRemboursement(StoreRemboursementRequest $request): RedirectResponse
    {
        $client = Client::query()->findOrFail($request->integer('client_id'));
        $operation = $this->portefeuille->createRemboursement($client, $request->validated(), $request->user());

        return redirect()
            ->route('operations.recu', $operation)
            ->with('success', 'Remboursement '.$operation->reference.' enregistré.');
    }

    public function recu(Request $request, Operation $operation): View|Response
    {
        $this->authorize('view', $operation);
        $operation->loadMissing(['client', 'typeOperation', 'user']);

        abort_unless(! $operation->estCredit(), 404);

        if ($request->input('export') === 'pdf') {
            return $this->documents->recuRemboursement($operation, $request->input('format', 'a4'));
        }

        return view('operations.recu', [
            'operation' => $operation,
            'donnees' => $this->documents->withOrganisation(['operation' => $operation]),
            'format' => $request->input('format', 'a4'),
        ]);
    }

    public function createCorrection(Operation $operation): View
    {
        $this->authorize('correct', $operation);
        $operation->loadMissing(['client', 'typeOperation']);

        return view('operations.correct', compact('operation'));
    }

    public function storeCorrection(CorrectOperationRequest $request, Operation $operation): RedirectResponse
    {
        $correction = $this->portefeuille->correcter($operation, $request->string('motif')->toString(), $request->user());

        return redirect()
            ->route('operations.show', $correction)
            ->with('success', 'Correction enregistrée : '.$correction->reference);
    }

    private function liste(Request $request, string $titre, ?int $typeFixe = null): View|Response
    {
        $query = $this->filteredQuery($request);

        if ($request->input('export') === 'excel') {
            $rows = $query->get()->map(fn (Operation $op) => [
                $op->date_operation?->format('d/m/Y'),
                $op->reference,
                $op->client?->code_client.' · '.$op->client?->nomComplet(),
                $op->typeOperation?->libelle,
                (float) $op->montant,
                (float) $op->solde_avant,
                (float) $op->solde_apres,
                $op->user?->name,
            ])->all();

            return $this->xlsx->download(
                str($titre)->slug().'-operations',
                $titre,
                ['Date', 'Référence', 'Client', 'Type', 'Montant', 'Solde avant', 'Solde après', 'Utilisateur'],
                $rows
            );
        }

        if ($request->input('export') === 'pdf') {
            $operations = $query->limit(2000)->get();

            return $this->documents->pdf('documents.operations', [
                'operations' => $operations,
                'titre' => $titre,
            ], str($titre)->slug().'-operations.pdf', 'a4', 'landscape');
        }

        $operations = $request->boolean('print')
            ? $query->limit(2000)->get()
            : $query->paginate(25)->withQueryString();

        return view($request->boolean('print') ? 'operations.print' : 'operations.index', [
            'operations' => $operations,
            'clients' => Client::query()->orderBy('nom')->get(['id', 'code_client', 'nom', 'prenom', 'solde']),
            'types' => TypeOperation::query()->orderBy('id')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'titre' => $titre,
            'typeFixe' => $typeFixe,
        ]);
    }

    private function filteredQuery(Request $request)
    {
        return Operation::query()
            ->with(['client', 'typeOperation', 'user'])
            ->filtered($request)
            ->orderByDesc('date_operation')
            ->orderByDesc('heure_operation')
            ->orderByDesc('id');
    }
}
