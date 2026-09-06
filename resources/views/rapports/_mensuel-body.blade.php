<table class="min-w-full text-sm">
    <thead class="bg-slate-50 text-left text-slate-500">
        <tr>
            <th class="px-3 py-2">Mois</th>
            <th class="px-3 py-2 text-right">Crédits</th>
            <th class="px-3 py-2 text-right">Remboursements</th>
            <th class="px-3 py-2 text-right">Encours</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
        @forelse ($lignes as $ligne)
            <tr>
                <td class="px-3 py-2">{{ $ligne['label'] }}</td>
                <td class="px-3 py-2 text-right">{{ \App\Support\Money::format($ligne['credits']) }}</td>
                <td class="px-3 py-2 text-right">{{ \App\Support\Money::format($ligne['remboursements']) }}</td>
                <td class="px-3 py-2 text-right font-medium">{{ \App\Support\Money::format($ligne['encours']) }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="px-3 py-6 text-center text-slate-500">Aucune opération.</td></tr>
        @endforelse
    </tbody>
</table>
