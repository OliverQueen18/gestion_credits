<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Reçu {{ $operation->reference }}</title>
    @include('documents._styles')
</head>
<body class="ticket">
    <div style="text-align:center;">
        @if (!empty($logo))
            <img src="{{ $logo }}" alt="Logo" class="logo"><br>
        @endif
        <strong>{{ $organisation }}</strong>
        <h1>Reçu</h1>
    </div>
    <p>Réf. {{ $operation->reference }}</p>
    <p>{{ $operation->date_operation?->format('d/m/Y') }} {{ $operation->heure_operation }}</p>
    <p>{{ $operation->client?->code_client }} — {{ $operation->client?->nomComplet() }}</p>
    <p><strong>Payé : {{ $operation->montantFormate() }}</strong></p>
    <p>Avant : {{ \App\Support\Money::format($operation->solde_avant) }}</p>
    <p>Après : {{ \App\Support\Money::format($operation->solde_apres) }}</p>
    <p>{{ $operation->mode_paiement ?: '' }}</p>
    <p>{{ $operation->observation }}</p>
    <p>Gestionnaire : {{ $operation->user?->name }}</p>
    <p>Signature :</p>
    <p>________________</p>
</body>
</html>
