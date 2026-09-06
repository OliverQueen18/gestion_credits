<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Historique des opérations</h1>
    </x-slot>

    <div class="bg-white border border-slate-200 rounded-lg p-6 space-y-4">
        <p class="text-sm text-slate-600">Recherchez dans le journal global, puis exportez ou imprimez le résultat.</p>
        <form method="GET" action="{{ route('operations.index') }}" class="grid md:grid-cols-3 gap-3 text-sm">
            <input type="text" name="reference" placeholder="Référence" class="rounded-md border-slate-300">
            <select name="client_id" class="rounded-md border-slate-300">
                <option value="">Tous les clients</option>
                @foreach ($clients as $c)
                    <option value="{{ $c->id }}">{{ $c->code_client }} — {{ $c->nom }} {{ $c->prenom }}</option>
                @endforeach
            </select>
            <select name="type" class="rounded-md border-slate-300">
                <option value="">Tous les types</option>
                @foreach ($types as $type)
                    <option value="{{ $type->id }}">{{ $type->libelle }}</option>
                @endforeach
            </select>
            <select name="user_id" class="rounded-md border-slate-300">
                <option value="">Tous les utilisateurs</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
            <input type="date" name="du" class="rounded-md border-slate-300">
            <input type="date" name="au" class="rounded-md border-slate-300">
            <div class="md:col-span-3">
                <x-primary-button>Ouvrir l’historique</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
