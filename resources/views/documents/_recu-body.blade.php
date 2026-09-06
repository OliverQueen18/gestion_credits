@include('documents._entete')
<h1>Reçu de remboursement</h1>
<div class="box">
    <table>
        <tr><td>Référence</td><td>{{ $operation->reference }}</td></tr>
        <tr><td>Date</td><td>{{ $operation->date_operation?->format('d/m/Y') }} {{ $operation->heure_operation }}</td></tr>
        <tr><td>Client</td><td>{{ $operation->client?->nomComplet() }}</td></tr>
        <tr><td>Code client</td><td>{{ $operation->client?->code_client }}</td></tr>
        <tr><td>Montant payé</td><td><strong>{{ $operation->montantFormate() }}</strong></td></tr>
        <tr><td>Ancien encours</td><td>{{ \App\Support\Money::format($operation->solde_avant) }}</td></tr>
        <tr><td>Nouveau solde</td><td>{{ \App\Support\Money::format($operation->solde_apres) }}</td></tr>
        <tr><td>Mode de paiement</td><td>{{ $operation->mode_paiement ?: '—' }}</td></tr>
        <tr><td>Observation</td><td>{{ $operation->observation ?: '—' }}</td></tr>
    </table>
</div>
<div class="signature">
    <p>Gestionnaire : {{ $operation->user?->name }}</p>
    <p>Signature : ______________________________</p>
</div>
