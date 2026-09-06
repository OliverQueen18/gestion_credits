<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $titre }}</title>
    @include('documents._styles')
</head>
<body>
    @include('documents._entete')
    <h1>{{ $titre }}</h1>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Référence</th>
                <th>Client</th>
                <th>Type</th>
                <th class="right">Montant</th>
                <th class="right">Avant</th>
                <th class="right">Après</th>
                <th>Utilisateur</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($operations as $operation)
                <tr>
                    <td>{{ $operation->date_operation?->format('d/m/Y') }}</td>
                    <td>{{ $operation->reference }}</td>
                    <td>{{ $operation->client?->code_client }} {{ $operation->client?->nomComplet() }}</td>
                    <td>{{ $operation->typeOperation?->libelle }}</td>
                    <td class="right">{{ $operation->montantFormate() }}</td>
                    <td class="right">{{ \App\Support\Money::format($operation->solde_avant) }}</td>
                    <td class="right">{{ \App\Support\Money::format($operation->solde_apres) }}</td>
                    <td>{{ $operation->user?->name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
