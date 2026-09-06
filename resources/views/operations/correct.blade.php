<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Corriger {{ $operation->reference }}</h1>
    </x-slot>

    <form method="POST" action="{{ route('operations.correction.store', $operation) }}" class="max-w-xl bg-white border border-slate-200 rounded-lg p-6 space-y-4">
        @csrf
        <p class="text-sm text-slate-600">
            Une opération inverse de <strong>{{ $operation->montantFormate() }}</strong> sera enregistrée.
            L’opération d’origine est conservée. Seule la dernière opération du client peut être corrigée.
        </p>
        <div>
            <x-input-label for="motif" value="Motif de la correction" />
            <textarea id="motif" name="motif" rows="4" class="mt-1 block w-full rounded-md border-slate-300" required>{{ old('motif') }}</textarea>
            <x-input-error :messages="$errors->get('motif')" class="mt-2" />
            <x-input-error :messages="$errors->get('operation')" class="mt-2" />
        </div>
        <div class="flex gap-3">
            <x-primary-button>Enregistrer la correction</x-primary-button>
            <a href="{{ route('operations.show', $operation) }}" class="text-sm text-slate-600 py-2">Annuler</a>
        </div>
    </form>
</x-app-layout>
