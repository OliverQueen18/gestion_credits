<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Nouveau remboursement</h1>
    </x-slot>

    @php
        $selected = old('client_id', $client?->id);
        $clientsJson = $clients->mapWithKeys(fn ($c) => [$c->id => (float) $c->solde]);
        $autoriseNegatif = \App\Models\Setting::allowsNegativeBalance();
    @endphp

    <form
        method="POST"
        action="{{ route('remboursements.store') }}"
        class="max-w-2xl bg-white border border-slate-200 rounded-lg p-6 space-y-4"
        x-data="{
            clients: {{ $clientsJson->toJson() }},
            clientId: '{{ $selected }}',
            montant: '{{ old('montant', '') }}',
            autoriseNegatif: {{ $autoriseNegatif ? 'true' : 'false' }},
            encours() { return Number(this.clients[this.clientId] ?? 0) },
            nouveau() { return this.encours() - Number(this.montant || 0) },
            depasse() { return Number(this.montant || 0) > this.encours() },
            format(n) { return new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' FCFA' }
        }"
        @submit.prevent="if (depasse() && !autoriseNegatif) { alert('Le montant du remboursement ne peut pas être supérieur à l’encours du client.'); return } if (confirm('Confirmer le remboursement de ' + format(Number(montant || 0)) + ' ? Nouveau solde : ' + format(nouveau()))) $el.submit()"
    >
        @csrf
        <div>
            <x-input-label for="client_id" value="Client" />
            <select id="client_id" name="client_id" x-model="clientId" class="mt-1 block w-full rounded-md border-slate-300" required>
                <option value="">Sélectionner…</option>
                @foreach ($clients as $c)
                    <option value="{{ $c->id }}" @selected((string) $selected === (string) $c->id)>{{ $c->code_client }} — {{ $c->nomComplet() }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
        </div>
        <div class="rounded-md bg-slate-50 border border-slate-200 p-3 text-sm">
            Encours actuel : <strong x-text="format(encours())"></strong>
        </div>
        <div>
            <x-input-label for="montant" value="Montant du remboursement" />
            <x-text-input id="montant" name="montant" type="number" step="1" min="1" class="mt-1 block w-full" x-model="montant" :value="old('montant')" required />
            <x-input-error :messages="$errors->get('montant')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="date_operation" value="Date" />
            <x-text-input id="date_operation" name="date_operation" type="date" class="mt-1 block w-full" :value="old('date_operation', now()->toDateString())" required />
        </div>
        <div>
            <x-input-label for="mode_paiement" value="Mode de paiement" />
            <select id="mode_paiement" name="mode_paiement" class="mt-1 block w-full rounded-md border-slate-300">
                <option value="">—</option>
                @foreach (['Espèces', 'Chèque', 'Virement', 'Mobile money', 'Autre'] as $mode)
                    <option value="{{ $mode }}" @selected(old('mode_paiement') === $mode)>{{ $mode }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label for="observation" value="Observation" />
            <textarea id="observation" name="observation" rows="3" class="mt-1 block w-full rounded-md border-slate-300">{{ old('observation') }}</textarea>
        </div>

        <div class="rounded-md bg-emerald-50 border border-emerald-100 p-4 text-sm space-y-1">
            <p>Nouveau solde : <strong x-text="format(nouveau())"></strong></p>
        </div>

        <div x-show="depasse()" x-cloak class="rounded-md bg-orange-50 border border-orange-200 text-orange-800 px-4 py-3 text-sm">
            Le montant du remboursement ne peut pas être supérieur à l’encours du client.
        </div>

        <div class="flex gap-3">
            <x-primary-button>Valider le remboursement</x-primary-button>
            <a href="{{ url()->previous() }}" class="text-sm text-slate-600 py-2">Annuler</a>
        </div>
    </form>
</x-app-layout>
