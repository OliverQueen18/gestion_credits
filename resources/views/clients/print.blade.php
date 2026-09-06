<x-print-layout title="État de compte">
    @include('documents._etat-compte-body', [
        'organisation' => \App\Models\Setting::getValue('organization_name', config('app.name')),
        'logo' => app(\App\Services\DocumentService::class)->logoDataUri(),
        'imprimeLe' => now()->format('d/m/Y H:i'),
        'operations' => $historique,
    ])
</x-print-layout>
