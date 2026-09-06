<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Types d’opérations</h1>
    </x-slot>

    <div class="space-y-4">
        <div class="flex justify-end">
            <a href="{{ route('type-operations.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-800 text-white text-xs font-semibold uppercase rounded-md">Nouveau type</a>
        </div>
        <div class="bg-white border border-slate-200 rounded-lg overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Libellé</th>
                        <th class="px-4 py-3">Sens</th>
                        <th class="px-4 py-3">Actif</th>
                        <th class="px-4 py-3">Opérations</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($types as $type)
                        <tr>
                            <td class="px-4 py-3 font-mono">{{ $type->code }}</td>
                            <td class="px-4 py-3">{{ $type->libelle }}</td>
                            <td class="px-4 py-3">{{ $type->sens->label() }}</td>
                            <td class="px-4 py-3">{{ $type->actif ? 'Oui' : 'Non' }}</td>
                            <td class="px-4 py-3">{{ $type->operations_count }}</td>
                            <td class="px-4 py-3"><a href="{{ route('type-operations.edit', $type) }}" class="text-brand-700 hover:underline">Modifier</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
