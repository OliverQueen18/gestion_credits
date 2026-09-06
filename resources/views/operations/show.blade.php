<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Opération {{ $operation->reference }}</h1>
    </x-slot>

    <div class="space-y-4 max-w-3xl">
        <div class="bg-white border border-slate-200 rounded-lg p-6 space-y-2 text-sm">
            <p><span class="text-slate-500">Client :</span> <a href="{{ route('clients.show', $operation->client_id) }}" class="text-brand-700 hover:underline">{{ $operation->client?->code_client }} · {{ $operation->client?->nomComplet() }}</a></p>
            <p><span class="text-slate-500">Type :</span> {{ $operation->typeOperation?->libelle }}</p>
            <p><span class="text-slate-500">Date :</span> {{ $operation->date_operation?->format('d/m/Y') }} {{ $operation->heure_operation }}</p>
            <p><span class="text-slate-500">Montant :</span> <strong>{{ $operation->montantFormate() }}</strong></p>
            <p><span class="text-slate-500">Solde avant / après :</span> {{ \App\Support\Money::format($operation->solde_avant) }} → {{ \App\Support\Money::format($operation->solde_apres) }}</p>
            <p><span class="text-slate-500">Mode de paiement :</span> {{ $operation->mode_paiement ?: '—' }}</p>
            <p><span class="text-slate-500">Observation :</span> {{ $operation->observation ?: '—' }}</p>
            <p><span class="text-slate-500">Utilisateur :</span> {{ $operation->user?->name }}</p>
            @if ($operation->est_annulee)
                <p class="text-orange-700">Cette opération est annulée.</p>
            @endif
        </div>

        <div class="flex flex-wrap gap-2">
            @if (! $operation->estCredit())
                <a href="{{ route('operations.recu', $operation) }}" class="inline-flex items-center px-4 py-2 bg-accent-700 text-white text-xs font-semibold uppercase rounded-md">Reçu</a>
            @endif
            @can('correct', $operation)
                @if ($operation->corrections->isEmpty())
                    <a href="{{ route('operations.correction.create', $operation) }}" class="inline-flex items-center px-4 py-2 bg-white border border-orange-300 text-orange-800 text-xs font-semibold uppercase rounded-md">Corriger</a>
                @endif
            @endcan
            <a href="{{ route('clients.show', $operation->client_id) }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-semibold uppercase rounded-md">Fiche client</a>
        </div>

        @if ($operation->corrections->isNotEmpty())
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 text-sm">
                <p class="font-medium mb-2">Corrections</p>
                @foreach ($operation->corrections as $correction)
                    <p>{{ $correction->created_at?->format('d/m/Y H:i') }} — {{ $correction->user?->name }} : {{ $correction->motif }}
                        @if ($correction->operationCorrection)
                            ({{ $correction->operationCorrection->reference }})
                        @endif
                    </p>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
