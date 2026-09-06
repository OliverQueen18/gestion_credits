<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">{{ $titre }}</h1>
    </x-slot>

    <div class="space-y-4">
        <form method="GET" class="bg-white border border-slate-200 rounded-lg p-4 grid md:grid-cols-4 gap-3 text-sm">
            <input type="text" name="reference" value="{{ request('reference') }}" placeholder="Référence" class="rounded-md border-slate-300">
            <select name="client_id" class="rounded-md border-slate-300">
                <option value="">Tous les clients</option>
                @foreach ($clients as $c)
                    <option value="{{ $c->id }}" @selected(request('client_id') == $c->id)>{{ $c->code_client }} — {{ $c->nom }} {{ $c->prenom }}</option>
                @endforeach
            </select>
            @unless (isset($typeFixe))
                <select name="type" class="rounded-md border-slate-300">
                    <option value="">Tous les types</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->id }}" @selected(request('type') == $type->id)>{{ $type->libelle }}</option>
                    @endforeach
                </select>
            @endunless
            <select name="user_id" class="rounded-md border-slate-300">
                <option value="">Tous les utilisateurs</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
            <input type="date" name="du" value="{{ request('du') }}" class="rounded-md border-slate-300">
            <input type="date" name="au" value="{{ request('au') }}" class="rounded-md border-slate-300">
            <input type="number" name="montant_min" value="{{ request('montant_min') }}" placeholder="Montant min" class="rounded-md border-slate-300">
            <input type="number" name="montant_max" value="{{ request('montant_max') }}" placeholder="Montant max" class="rounded-md border-slate-300">
            <div class="md:col-span-4 flex flex-wrap items-center gap-3">
                <x-primary-button>Filtrer</x-primary-button>
                <x-export-buttons :route="request()->route()->getName()" />
            </div>
        </form>

        <div class="bg-white border border-slate-200 rounded-lg overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-3 py-2">Date</th>
                        <th class="px-3 py-2">Référence</th>
                        <th class="px-3 py-2">Client</th>
                        <th class="px-3 py-2">Type</th>
                        <th class="px-3 py-2 text-right">Montant</th>
                        <th class="px-3 py-2 text-right">Solde avant</th>
                        <th class="px-3 py-2 text-right">Solde après</th>
                        <th class="px-3 py-2">Utilisateur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($operations as $operation)
                        <tr>
                            <td class="px-3 py-2 whitespace-nowrap">{{ $operation->date_operation?->format('d/m/Y') }}</td>
                            <td class="px-3 py-2 font-mono text-xs">
                                <a href="{{ route('operations.show', $operation) }}" class="text-brand-700 hover:underline">{{ $operation->reference }}</a>
                            </td>
                            <td class="px-3 py-2">
                                <a href="{{ route('clients.show', $operation->client_id) }}" class="text-brand-700 hover:underline">
                                    {{ $operation->client?->code_client }} · {{ $operation->client?->nomComplet() }}
                                </a>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-8 text-center text-slate-500">Aucune opération.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $operations->links() }}
    </div>
</x-app-layout>
