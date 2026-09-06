<table class="min-w-full text-sm">
    <thead class="bg-slate-50 text-left text-slate-500">
        <tr>
            <th class="px-3 py-2">Code</th>
            <th class="px-3 py-2">Nom</th>
            <th class="px-3 py-2">Téléphone</th>
            <th class="px-3 py-2 text-right">Encours</th>
            <th class="px-3 py-2">Dernier crédit</th>
            <th class="px-3 py-2">Dernier remboursement</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
        @forelse ($lignes as $ligne)
            <tr>
                <td class="px-3 py-2 font-mono text-xs">
                    @if (empty($impression))
                        <a href="{{ route('clients.show', $ligne['id']) }}" class="text-brand-700 hover:underline">{{ $ligne['code'] }}</a>
                    @else
                        {{ $ligne['code'] }}
                    @endif
                </td>
                <td class="px-3 py-2">{{ $ligne['nom'] }}</td>
                <td class="px-3 py-2">{{ $ligne['telephone'] ?: '—' }}</td>
                <td class="px-3 py-2 text-right font-medium">{{ $ligne['encours_libelle'] }}</td>
                <td class="px-3 py-2">{{ $ligne['dernier_credit'] }}</td>
                <td class="px-3 py-2">{{ $ligne['dernier_remboursement'] }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-3 py-6 text-center text-slate-500">Aucun débiteur.</td></tr>
        @endforelse
    </tbody>
</table>
