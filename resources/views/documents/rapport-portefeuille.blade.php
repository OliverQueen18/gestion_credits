<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Situation portefeuille</title>
    @include('documents._styles')
</head>
<body>
    @include('documents._entete')
    <h1>Situation du portefeuille</h1>
    <table>
        <tr><td>Total crédits</td><td class="right">{{ \App\Support\Money::format($rapport['total_credits']) }}</td></tr>
        <tr><td>Total remboursements</td><td class="right">{{ \App\Support\Money::format($rapport['total_remboursements']) }}</td></tr>
        <tr><td>Encours</td><td class="right">{{ \App\Support\Money::format($rapport['encours']) }}</td></tr>
        <tr><td>Nombre de clients</td><td class="right">{{ $rapport['clients'] }}</td></tr>
        <tr><td>Nombre de débiteurs</td><td class="right">{{ $rapport['debiteurs'] }}</td></tr>
    </table>
</body>
</html>
