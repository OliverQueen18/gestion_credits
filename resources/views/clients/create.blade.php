<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Nouveau client</h1>
    </x-slot>

    <form method="POST" action="{{ route('clients.store') }}" class="max-w-3xl bg-white border border-slate-200 rounded-lg p-6 space-y-6">
        @include('clients._form', ['client' => null])
        <div class="flex gap-3">
            <x-primary-button>Enregistrer</x-primary-button>
            <a href="{{ route('clients.index') }}" class="text-sm text-slate-600 py-2">Annuler</a>
        </div>
    </form>
</x-app-layout>
