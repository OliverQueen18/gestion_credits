<div>
    <x-input-label for="code" value="Code" />
    <x-text-input id="code" name="code" class="mt-1 block w-full" :value="old('code', $type?->code ?? '')" required />
    <x-input-error :messages="$errors->get('code')" class="mt-2" />
</div>
<div>
    <x-input-label for="libelle" value="Libellé" />
    <x-text-input id="libelle" name="libelle" class="mt-1 block w-full" :value="old('libelle', $type?->libelle ?? '')" required />
    <x-input-error :messages="$errors->get('libelle')" class="mt-2" />
</div>
<div>
    <x-input-label for="sens" value="Sens" />
    <select id="sens" name="sens" class="mt-1 block w-full rounded-md border-slate-300" required>
        @foreach ($sens as $s)
            <option value="{{ $s->value }}" @selected((string) old('sens', $type?->sens?->value ?? '') === (string) $s->value)>{{ $s->label() }} ({{ $s->value }})</option>
        @endforeach
    </select>
</div>
<label class="inline-flex items-center gap-2 text-sm">
    <input type="checkbox" name="actif" value="1" class="rounded border-slate-300" @checked(old('actif', $type?->actif ?? true))>
    Actif
</label>
