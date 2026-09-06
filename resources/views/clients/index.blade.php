<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Clients</h1>
    </x-slot>

    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <form method="GET" class="flex flex-wrap gap-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Code, nom, prénom, téléphone, e-mail" class="rounded-md border-slate-300 text-sm w-64">
                <select name="statut" class="rounded-md border-slate-300 text-sm">
                    <option value="">Tous les statuts</option>
                    <option value="1" @selected(request('statut') === '1')>Actif</option>
                    <option value="0" @selected(request('statut') === '0')>Inactif</option>
                </select>
                <select name="encours" class="rounded-md border-slate-300 text-sm">
                    <option value="">Tous les encours</option>
                    <option value="oui" @selected(request('encours') === 'oui')>Avec encours</option>
                    <option value="non" @selected(request('encours') === 'non')>Sans encours</option>
                </select>
                <x-primary-button>Filtrer</x-primary-button>
            </form>
            @can('create', App\Models\Client::class)
                <a href="{{ route('clients.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-800 text-white text-xs font-semibold uppercase rounded-md">Nouveau client</a>
            @endcan
        </div>

        <div class="bg-white border border-slate-200 rounded-lg overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Code</th>
                        <th class="px-4 py-3 font-medium">Client</th>
                        <th class="px-4 py-3 font-medium">Téléphone</th>
                        <th class="px-4 py-3 font-medium text-right">Encours</th>
                        <th class="px-4 py-3 font-medium">Statut</th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($clients as $client)
                        <tr>
                            <td class="px-4 py-3 font-mono">{{ $client->code_client }}</td>
                            <td class="px-4 py-3">{{ $client->nomComplet() }}</td>
                            <td class="px-4 py-3">{{ $client->telephone ?: '—' }}</td>
                            <td class="px-4 py-3 text-right font-medium">{{ $client->soldeFormate() }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs {{ $client->estActif() ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $client->statut->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 space-x-2 whitespace-nowrap">
                                <a href="{{ route('clients.show', $client) }}" class="text-brand-700 hover:underline">Voir</a>
                                @can('update', $client)
                                    <a href="{{ route('clients.edit', $client) }}" class="text-slate-600 hover:underline">Modifier</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">Aucun client.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $clients->links() }}
    </div>
</x-app-layout>
