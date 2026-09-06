<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Impression' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon-gestion-credit.png') }}">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1e293b; font-size: 12px; margin: 24px; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        h2 { font-size: 14px; margin: 16px 0 8px; }
        .muted { color: #64748b; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 2px solid #0f172a; padding-bottom: 12px; }
        .logo { max-height: 56px; max-width: 140px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        th { background: #f1f5f9; }
        .right { text-align: right; }
        .kpis td { width: 50%; }
        .no-print { margin: 16px 0; }
        @media print { .no-print { display: none !important; } body { margin: 12mm; } }
    </style>
</head>
<body>
    <div class="no-print">
        <button type="button" onclick="window.print()">Imprimer</button>
        <a href="{{ url()->previous() }}">Retour</a>
    </div>
    {{ $slot }}
</body>
</html>
