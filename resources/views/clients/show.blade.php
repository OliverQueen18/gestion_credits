<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">{{ $client->nomComplet() }}</h1>
    </x-slot>

    <div class="space-y-6">
        <div class="grid lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 bg-white border border-slate-200 rounded-lg p-6 space-y-2">
                <p><span class="text-slate-500">Code client :</span> <span class="font-mono">{{ $client->code_client }}</span></p>
                <p><span class="text-slate-500">Téléphone :</span> {{ $client->telephone ?: '—' }}</p>
                <p><span class="text-slate-500">E-mail :</span>
                    @if ($client->email)
                        <a href="mailto:{{ $client->email }}" class="text-brand-700 hover:underline">{{ $client->email }}</a>
                    @else
                        —
                    @endif
                </p>
                <p><span class="text-slate-500">Adresse :</span> {{ $client->adresse ?: '—' }}</p>
                <p>
                    <span class="text-slate-500">Statut :</span>
                    <span class="px-2 py-0.5 rounded text-xs {{ $client->estActif() ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $client->statut->label() }}</span>
                </p>
            </div>
            <div class="bg-brand-800 text-white rounded-lg p-6">
                <p class="text-xs uppercase tracking-wider text-gold-400">Encours actuel</p>
                <p class="mt-2 text-3xl font-semibold">{{ $client->soldeFormate() }}</p>
                <p class="mt-3 text-xs text-brand-100">Crédits {{ \App\Support\Money::format($totaux['credits']) }} · Remb. {{ \App\Support\Money::format($totaux['remboursements']) }}</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            @can('create', App\Models\Operation::class)
                @if ($client->estActif())
                    <a href="{{ route('credits.create', ['client' => $client->id]) }}" class="inline-flex items-center px-4 py-2 bg-brand-700 text-white text-xs font-semibold uppercase rounded-md">Nouveau crédit</a>
                    <a href="{{ route('remboursements.create', ['client' => $client->id]) }}" class="inline-flex items-center px-4 py-2 bg-accent-700 text-white text-xs font-semibold uppercase rounded-md">Nouveau remboursement</a>
                @endif
            @endcan
            @can('update', $client)
                <a href="{{ route('clients.edit', $client) }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-semibold uppercase rounded-md">Modifier</a>
                <form method="POST" action="{{ route('clients.toggle-status', $client) }}">
                    @csrf
                    @method('PATCH')
                    <button class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-semibold uppercase rounded-md">
                        {{ $client->estActif() ? 'Désactiver' : 'Activer' }}
                    </button>
                </form>
            @endcan
            <a href="{{ route('clients.show', [$client, 'print' => 1] + request()->except('export', 'print')) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-semibold uppercase rounded-md">Imprimer</a>
            <a href="{{ route('clients.show', [$client, 'export' => 'pdf'] + request()->except('export', 'print')) }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-semibold uppercase rounded-md">Exporter PDF</a>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg p-4">
            <h2 class="font-medium mb-3">Historique financier</h2>
            <form method="GET" class="flex flex-wrap gap-2 mb-4">
                <input type="date" name="du" value="{{ request('du') }}" class="rounded-md border-slate-300 text-sm">
                <input type="date" name="au" value="{{ request('au') }}" class="rounded-md border-slate-300 text-sm">
                <select name="type" class="rounded-md border-slate-300 text-sm">
                    <option value="">Tous les types</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->id }}" @selected(request('type') == $type->id)>{{ $type->libelle }}</option>
                    @endforeach
                </select>
                <x-primary-button>Filtrer</x-primary-button>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-3 py-2">Date</th>
                            <th class="px-3 py-2">Référence</th>
                            <th class="px-3 py-2">Type</th>
                            <th class="px-3 py-2 text-right">Montant</th>
                            <th class="px-3 py-2 text-right">Solde avant</th>
                            <th class="px-3 py-2 text-right">Solde après</th>
                            <th class="px-3 py-2">Utilisateur</th>
                            <th class="px-3 py-2">Observation</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($operations as $operation)
                            <tr>
                                <td class="px-3 py-2 whitespace-nowrap">{{ $operation->date_operation?->format('d/m/Y') }} {{ $operation->heure_operation }}</td>
                                <td class="px-3 py-2 font-mono text-xs">
                                    <a href="{{ route('operations.show', $operation) }}" class="text-brand-700 hover:underline">{{ $operation->reference }}</a>
                                </td>
                                <td class="px-3 py-2">
                                    <span class="px-2 py-0.5 rounded text-xs {{ $operation->typeOperation?->incrementeEncours() ? 'bg-brand-50 text-brand-800' : 'bg-emerald-50 text-emerald-800' }}">
                                        {{ $operation->typeOperation?->libelle }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-right">{{ $operation->montantFormate() }}</td>
                                <td class="px-3 py-2 text-right">{{ \App\Support\Money::format($operation->solde_avant) }}</td>
                                <td class="px-3 py-2 text-right">{{ \App\Support\Money::format($operation->solde_apres) }}</td>
                                <td class="px-3 py-2">{{ $operation->user?->name }}</td>
                                <td class="px-3 py-2 max-w-xs truncate" title="{{ $operation->observation }}">{{ $operation->observation }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-3 py-6 text-center text-slate-500">Aucune opération.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $operations->links() }}</div>
        </div>
    </div>
</x-app-layout>
