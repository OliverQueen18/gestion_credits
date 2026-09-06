<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold text-slate-800">Tableau de bord</h1>
    </x-slot>

    <div class="space-y-6">
        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach ([
                ['Clients', number_format($kpis['clients'], 0, ',', ' ')],
                ['Clients avec encours', number_format($kpis['clients_encours'], 0, ',', ' ')],
                ['Encours total', \App\Support\Money::format($kpis['encours'])],
                ['Opérations', number_format($kpis['operations'], 0, ',', ' ')],
                ['Total crédits', \App\Support\Money::format($kpis['total_credits'])],
                ['Total remboursements', \App\Support\Money::format($kpis['total_remboursements'])],
                ['Crédits du mois', \App\Support\Money::format($kpis['credits_mois'])],
                ['Remboursements du mois', \App\Support\Money::format($kpis['remboursements_mois'])],
            ] as [$label, $value])
                <div class="bg-white border border-slate-200 rounded-lg p-4">
                    <p class="text-xs uppercase tracking-wider text-slate-500">{{ $label }}</p>
                    <p class="mt-2 text-xl font-semibold text-slate-800">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid lg:grid-cols-2 gap-4">
            <div class="bg-white border border-slate-200 rounded-lg p-4">
                <h2 class="font-medium mb-3">Crédits vs remboursements</h2>
                <canvas id="chartMensuel" height="140"></canvas>
            </div>
            <div class="bg-white border border-slate-200 rounded-lg p-4">
                <h2 class="font-medium mb-3">Évolution de l’encours</h2>
                <canvas id="chartEvolution" height="140"></canvas>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg p-4">
            <h2 class="font-medium mb-3">Répartition des clients par encours</h2>
            <canvas id="chartRepartition" height="90"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        const mensuel = @json($mensuel);
        const evolution = @json($evolution);
        const repartition = @json($repartition);

        new Chart(document.getElementById('chartMensuel'), {
            type: 'bar',
            data: {
                labels: mensuel.map(x => x.label),
                datasets: [
                    { label: 'Crédits', data: mensuel.map(x => x.credits), backgroundColor: '#0a3358' },
                    { label: 'Remboursements', data: mensuel.map(x => x.remboursements), backgroundColor: '#2ea043' },
                ],
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } },
        });

        new Chart(document.getElementById('chartEvolution'), {
            type: 'line',
            data: {
                labels: evolution.map(x => x.label),
                datasets: [{
                    label: 'Encours',
                    data: evolution.map(x => x.encours),
                    borderColor: '#0a3358',
                    backgroundColor: 'rgba(10, 51, 88, 0.10)',
                    fill: true,
                    tension: 0.25,
                }],
            },
            options: { responsive: true, plugins: { legend: { display: false } } },
        });

        new Chart(document.getElementById('chartRepartition'), {
            type: 'bar',
            data: {
                labels: repartition.map(x => x.label),
                datasets: [{
                    label: 'Clients',
                    data: repartition.map(x => x.nombre),
                    backgroundColor: ['#94a3b8', '#f0c43a', '#2ea043', '#1f5f9a', '#072544'],
                }],
            },
            options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } } },
        });
    </script>
</x-app-layout>
