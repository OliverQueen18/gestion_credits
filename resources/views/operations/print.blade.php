<x-print-layout :title="$titre">
    <h1>{{ $titre }}</h1>
    <p class="muted">Imprimé le {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Référence</th>
                <th>Client</th>
                <th>Type</th>
                <th class="right">Montant</th>
                <th class="right">Solde avant</th>
                <th class="right">Solde après</th>
                <th>Utilisateur</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($operations as $operation)
                <tr>
                    <td>{{ $operation->date_operation?->format('d/m/Y') }}</td>
                    <td>{{ $operation->reference }}</td>
                    <td>{{ $operation->client?->code_client }} · {{ $operation->client?->nomComplet() }}</td>
                    <td>{{ $operation->typeOperation?->libelle }}</td>
                    <td class="right">{{ $operation->montantFormate() }}</td>
                    <td class="right">{{ \App\Support\Money::format($operation->solde_avant) }}</td>
                    <td class="right">{{ \App\Support\Money::format($operation->solde_apres) }}</td>
                    <td>{{ $operation->user?->name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-print-layout>
