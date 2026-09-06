<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Rapport mensuel</title>
    @include('documents._styles')
</head>
<body>
    @include('documents._entete')
    <h1>Rapport mensuel</h1>
    <table>
        <thead>
            <tr>
                <th>Mois</th>
                <th class="right">Crédits</th>
                <th class="right">Remboursements</th>
                <th class="right">Encours</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lignes as $ligne)
                <tr>
                    <td>{{ $ligne['label'] }}</td>
                    <td class="right">{{ \App\Support\Money::format($ligne['credits']) }}</td>
                    <td class="right">{{ \App\Support\Money::format($ligne['remboursements']) }}</td>
                    <td class="right">{{ \App\Support\Money::format($ligne['encours']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
