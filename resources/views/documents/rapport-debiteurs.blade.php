<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Clients débiteurs</title>
    @include('documents._styles')
</head>
<body>
    @include('documents._entete')
    <h1>Clients débiteurs</h1>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Nom</th>
                <th>Téléphone</th>
                <th class="right">Encours</th>
                <th>Dernier crédit</th>
                <th>Dernier remboursement</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lignes as $ligne)
                <tr>
                    <td>{{ $ligne['code'] }}</td>
                    <td>{{ $ligne['nom'] }}</td>
                    <td>{{ $ligne['telephone'] ?: '—' }}</td>
                    <td class="right">{{ $ligne['encours_libelle'] }}</td>
                    <td>{{ $ligne['dernier_credit'] }}</td>
                    <td>{{ $ligne['dernier_remboursement'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
