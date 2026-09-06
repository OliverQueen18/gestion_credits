<h2 class="text-base font-semibold mb-4">Synthèse</h2>
<table class="min-w-full text-sm">
    <tbody class="divide-y divide-slate-100">
        <tr><td class="py-2 text-slate-500">Total crédits</td><td class="py-2 text-right font-medium">{{ \App\Support\Money::format($rapport['total_credits']) }}</td></tr>
        <tr><td class="py-2 text-slate-500">Total remboursements</td><td class="py-2 text-right font-medium">{{ \App\Support\Money::format($rapport['total_remboursements']) }}</td></tr>
        <tr><td class="py-2 text-slate-500">Encours</td><td class="py-2 text-right font-semibold">{{ \App\Support\Money::format($rapport['encours']) }}</td></tr>
        <tr><td class="py-2 text-slate-500">Nombre de clients</td><td class="py-2 text-right">{{ number_format($rapport['clients'], 0, ',', ' ') }}</td></tr>
        <tr><td class="py-2 text-slate-500">Nombre de débiteurs</td><td class="py-2 text-right">{{ number_format($rapport['debiteurs'], 0, ',', ' ') }}</td></tr>
    </tbody>
</table>
