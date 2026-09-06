@include('documents._entete')
<h1>État de compte client</h1>
<table>
    <tr><td>Nom</td><td>{{ $client->nomComplet() }}</td></tr>
    <tr><td>Code client</td><td>{{ $client->code_client }}</td></tr>
    <tr><td>Téléphone</td><td>{{ $client->telephone ?: '—' }}</td></tr>
    <tr><td>E-mail</td><td>{{ $client->email ?: '—' }}</td></tr>
    <tr><td>Adresse</td><td>{{ $client->adresse ?: '—' }}</td></tr>
    <tr><td>Statut</td><td>{{ $client->statut->label() }}</td></tr>
</table>
<h2>Résumé</h2>
<table>
    <tr><td>Total crédits</td><td class="right">{{ \App\Support\Money::format($totaux['credits']) }}</td></tr>
    <tr><td>Total remboursements</td><td class="right">{{ \App\Support\Money::format($totaux['remboursements']) }}</td></tr>
    <tr><td><strong>Encours actuel</strong></td><td class="right"><strong>{{ $client->soldeFormate() }}</strong></td></tr>
</table>
<h2>Détail des opérations</h2>
<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Référence</th>
            <th>Type</th>
            <th class="right">Crédit</th>
            <th class="right">Remboursement</th>
            <th class="right">Solde</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($operations as $operation)
            <tr>
                <td>{{ $operation->date_operation?->format('d/m/Y') }}</td>
                <td>{{ $operation->reference }}</td>
                <td>{{ $operation->typeOperation?->libelle }}</td>
                <td class="right">{{ $operation->estCredit() ? $operation->montantFormate() : '' }}</td>
                <td class="right">{{ $operation->estCredit() ? '' : $operation->montantFormate() }}</td>
                <td class="right">{{ \App\Support\Money::format($operation->solde_apres) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
