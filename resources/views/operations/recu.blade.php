<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Reçu {{ $operation->reference }}</h1>
    </x-slot>

    <div class="space-y-4">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('operations.recu', [$operation, 'export' => 'pdf', 'format' => 'a4']) }}" class="inline-flex items-center px-4 py-2 bg-brand-800 text-white text-xs font-semibold uppercase rounded-md">PDF A4</a>
            <a href="{{ route('operations.recu', [$operation, 'export' => 'pdf', 'format' => 'ticket']) }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-semibold uppercase rounded-md">PDF ticket</a>
            <button type="button" onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-semibold uppercase rounded-md">Imprimer</button>
            <a href="{{ route('clients.show', $operation->client_id) }}" class="inline-flex items-center px-4 py-2 text-slate-600 text-xs font-semibold uppercase">Fiche client</a>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg p-8 max-w-2xl print:border-0 print:shadow-none">
            @include('documents._recu-body', $donnees)
        </div>
    </div>
</x-app-layout>
