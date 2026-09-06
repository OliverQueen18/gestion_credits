<?php

namespace App\Http\Controllers;

use App\Enums\ClientStatut;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Models\TypeOperation;
use App\Services\AuditLogger;
use App\Services\DocumentService;
use App\Services\PortefeuilleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ClientController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly PortefeuilleService $portefeuille,
        private readonly DocumentService $documents,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Client::class);

        $clients = Client::query()
            ->search($request->string('q')->toString())
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', (int) $request->input('statut')))
            ->when($request->input('encours') === 'oui', fn ($q) => $q->avecEncours())
            ->when($request->input('encours') === 'non', fn ($q) => $q->where('solde', '<=', 0))
            ->orderBy('nom')
            ->orderBy('prenom')
            ->paginate(20)
            ->withQueryString();

        return view('clients.index', compact('clients'));
    }

    public function create(): View
    {
        $this->authorize('create', Client::class);

        $codePropose = Client::proposerCode();

        return view('clients.create', [
            'codePropose' => $codePropose,
            'numeroPropose' => preg_match('/(\d+)$/', $codePropose, $matches) === 1
                ? $matches[1]
                : '1',
        ]);
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $client = Client::query()->create([
            ...$request->validated(),
            'solde' => 0,
            'statut' => ClientStatut::Actif,
        ]);

        $this->audit->log('creation_client', $client, null, $client->only(['code_client', 'nom', 'prenom']));

        return redirect()->route('clients.show', $client)->with('success', 'Client créé.');
    }

    public function show(Request $request, Client $client): View|Response
    {
        $this->authorize('view', $client);

        $operations = $client->operations()
            ->with(['typeOperation', 'user'])
            ->when($request->filled('type'), fn ($q) => $q->where('type_operation_id', $request->integer('type')))
            ->when($request->filled('du'), fn ($q) => $q->whereDate('date_operation', '>=', $request->input('du')))
            ->when($request->filled('au'), fn ($q) => $q->whereDate('date_operation', '<=', $request->input('au')))
            ->orderByDesc('date_operation')
            ->orderByDesc('heure_operation')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $totaux = $this->portefeuille->totaux($client);
        $types = TypeOperation::query()->actif()->orderBy('id')->get();

        if ($request->boolean('print') || $request->input('export') === 'pdf') {
            $historique = $client->operations()
                ->with(['typeOperation', 'user'])
                ->when($request->filled('type'), fn ($q) => $q->where('type_operation_id', $request->integer('type')))
                ->when($request->filled('du'), fn ($q) => $q->whereDate('date_operation', '>=', $request->input('du')))
                ->when($request->filled('au'), fn ($q) => $q->whereDate('date_operation', '<=', $request->input('au')))
                ->orderBy('date_operation')
                ->orderBy('heure_operation')
                ->orderBy('id')
                ->get();

            if ($request->input('export') === 'pdf') {
                return $this->documents->etatCompte($client, $historique, $totaux);
            }

            return view('clients.print', compact('client', 'historique', 'totaux'));
        }

        return view('clients.show', compact('client', 'operations', 'totaux', 'types'));
    }

    public function edit(Client $client): View
    {
        $this->authorize('update', $client);

        return view('clients.edit', compact('client'));
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $avant = $client->only(['code_client', 'nom', 'prenom', 'telephone', 'email', 'adresse']);
        $client->update($request->validated());
        $this->audit->log('modification_client', $client, $avant, $client->only(['code_client', 'nom', 'prenom', 'telephone', 'email', 'adresse']));

        return redirect()->route('clients.show', $client)->with('success', 'Client mis à jour.');
    }

    public function toggleStatus(Client $client): RedirectResponse
    {
        $this->authorize('toggleStatus', $client);

        $avant = $client->statut;
        $client->update([
            'statut' => $client->estActif() ? ClientStatut::Inactif : ClientStatut::Actif,
        ]);

        $this->audit->log('modification_client', $client, ['statut' => $avant->value], ['statut' => $client->statut->value]);

        return back()->with('success', $client->estActif() ? 'Client activé.' : 'Client désactivé.');
    }
}
