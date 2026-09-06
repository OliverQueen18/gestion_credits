<div>
    <x-input-label for="name" value="Nom" />
    <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $user?->name)" required />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>
<div>
    <x-input-label for="username" value="Nom d’utilisateur" />
    <x-text-input id="username" name="username" class="mt-1 block w-full" :value="old('username', $user?->username)" required autocomplete="username" />
    <p class="mt-1 text-xs text-slate-500">Lettres, chiffres, point, tiret ou underscore. Sert aussi à la connexion.</p>
    <x-input-error :messages="$errors->get('username')" class="mt-2" />
</div>
<div>
    <x-input-label for="email" value="E-mail" />
    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user?->email)" required />
    <x-input-error :messages="$errors->get('email')" class="mt-2" />
</div>
<div>
    <x-input-label for="telephone" value="N° de téléphone" />
    <x-text-input id="telephone" name="telephone" class="mt-1 block w-full" :value="old('telephone', $user?->telephone)" autocomplete="tel" />
    <p class="mt-1 text-xs text-slate-500">Optionnel. Peut servir d’identifiant de connexion.</p>
    <x-input-error :messages="$errors->get('telephone')" class="mt-2" />
</div>
<div>
    <x-input-label for="role" value="Rôle" />
    <select id="role" name="role" class="mt-1 block w-full rounded-md border-slate-300" required>
        @foreach ($roles as $role)
            <option value="{{ $role->value }}" @selected(old('role', $user?->role?->value) === $role->value)>{{ $role->label() }}</option>
        @endforeach
    </select>
</div>
<div>
    <x-input-label for="password" :value="$user ? 'Nouveau mot de passe (optionnel)' : 'Mot de passe'" />
    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" :required="! $user" />
    <x-input-error :messages="$errors->get('password')" class="mt-2" />
</div>
<div>
    <x-input-label for="password_confirmation" value="Confirmation du mot de passe" />
    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" :required="! $user" />
</div>
<label class="inline-flex items-center gap-2 text-sm">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300" @checked(old('is_active', $user?->is_active ?? true))>
    Compte actif
</label>
