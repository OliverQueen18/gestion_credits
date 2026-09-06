<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Modifier {{ $client->nomComplet() }}</h1>
    </x-slot>

    <form method="POST" action="{{ route('clients.update', $client) }}" class="max-w-3xl bg-white border border-slate-200 rounded-lg p-6 space-y-6">
        @method('PUT')
        @include('clients._form', ['client' => $client])
        <div class="flex gap-3">
            <x-primary-button>Mettre à jour</x-primary-button>
            <a href="{{ route('clients.show', $client) }}" class="text-sm text-slate-600 py-2">Annuler</a>
        </div>
    </form>
</x-app-layout>
