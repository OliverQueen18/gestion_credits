<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Paramètres</h1>
    </x-slot>

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="max-w-xl bg-white border border-slate-200 rounded-lg p-6 space-y-4">
        @csrf
        @method('PUT')
        <div>
            <x-input-label for="organization_name" value="Nom de l’organisation" />
            <x-text-input id="organization_name" name="organization_name" class="mt-1 block w-full" :value="old('organization_name', $organization_name)" required />
        </div>
        <div>
            <x-input-label for="logo" value="Logo (reçus et PDF)" />
            <p class="mt-1 mb-2">
                @if ($logo_path)
                    <img src="{{ asset('storage/'.$logo_path) }}" alt="Logo" class="h-16 object-contain">
                @else
                    <img src="{{ asset('images/logo-gestion-credit.png') }}" alt="Logo par défaut" class="h-20 object-contain">
                    <span class="block text-xs text-slate-500 mt-1">Logo de l’application (utilisé tant qu’aucun fichier n’est importé)</span>
                @endif
            </p>
            @if ($logo_path)
                <label class="inline-flex items-center gap-2 text-sm mb-2">
                    <input type="checkbox" name="supprimer_logo" value="1" class="rounded border-slate-300">
                    Revenir au logo de l’application
                </label>
            @endif
            <input id="logo" name="logo" type="file" accept="image/*" class="mt-1 block w-full text-sm">
            <x-input-error :messages="$errors->get('logo')" class="mt-2" />
        </div>
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="hidden" name="allow_negative_balance" value="0">
            <input type="checkbox" name="allow_negative_balance" value="1" class="rounded border-slate-300" @checked(old('allow_negative_balance', $allow_negative_balance))>
            Autoriser exceptionnellement un solde négatif
        </label>
        <x-primary-button>Enregistrer</x-primary-button>
    </form>
</x-app-layout>
