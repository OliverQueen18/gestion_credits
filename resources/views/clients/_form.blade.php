@php
    $estCreation = blank($client?->id);
    $codeInitial = old('code_client', $client?->code_client ?? $codePropose ?? '');
    $numeroPropose = $numeroPropose ?? '';
@endphp
@csrf
<div
    class="grid sm:grid-cols-2 gap-4"
    x-data="{
        auto: {{ $estCreation && ! old('code_client') ? 'true' : 'false' }},
        numero: @js($numeroPropose),
        code: @js($codeInitial),
        nom: @js(old('nom', $client?->nom ?? '')),
        prenom: @js(old('prenom', $client?->prenom ?? '')),
        initiales() {
            const nom = (this.nom || '').trim();
            const prenom = (this.prenom || '').trim();
            const lettreNom = nom ? nom.charAt(0).toLocaleUpperCase('fr-FR') : 'C';
            const lettrePrenom = prenom ? prenom.charAt(0).toLocaleUpperCase('fr-FR') : 'L';
            return lettreNom + lettrePrenom;
        },
        appliquer() {
            if (this.auto && this.numero) {
                this.code = this.initiales() + String(this.numero).padStart(3, '0');
            }
        }
    }"
    x-init="appliquer()"
>
    <div>
        <x-input-label for="code_client" value="Code client" />
        <x-text-input id="code_client" name="code_client" class="mt-1 block w-full font-mono" x-model="code" x-on:input="auto = false" required />
        @if ($estCreation)
            <p class="mt-1 text-xs text-slate-500">Code proposé automatiquement. Vous pouvez le modifier.</p>
        @endif
        <x-input-error :messages="$errors->get('code_client')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="telephone" value="Téléphone" />
        <x-text-input id="telephone" name="telephone" class="mt-1 block w-full" :value="old('telephone', $client?->telephone ?? '')" />
        <x-input-error :messages="$errors->get('telephone')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="nom" value="Nom" />
        <x-text-input id="nom" name="nom" class="mt-1 block w-full" x-model="nom" x-on:input="appliquer()" required />
        <x-input-error :messages="$errors->get('nom')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="prenom" value="Prénom" />
        <x-text-input id="prenom" name="prenom" class="mt-1 block w-full" x-model="prenom" x-on:input="appliquer()" required />
        <x-input-error :messages="$errors->get('prenom')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="email" value="E-mail" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $client?->email ?? '')" autocomplete="email" />
        <p class="mt-1 text-xs text-slate-500">Facultatif.</p>
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="adresse" value="Adresse" />
        <x-text-input id="adresse" name="adresse" class="mt-1 block w-full" :value="old('adresse', $client?->adresse ?? '')" />
        <x-input-error :messages="$errors->get('adresse')" class="mt-2" />
    </div>
</div>
