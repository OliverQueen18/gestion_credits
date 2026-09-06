<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>État de compte {{ $client->code_client }}</title>
    @include('documents._styles')
</head>
<body>
    @include('documents._etat-compte-body')
</body>
</html>
