<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Operation;
use App\Models\Setting;
use App\Support\Money;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class DocumentService
{
    public function organizationName(): string
    {
        return (string) Setting::getValue('organization_name', config('portefeuille.organization_name'));
    }

    public function logoDataUri(): ?string
    {
        $full = Setting::logoFullPath();
        if (! $full) {
            return null;
        }

        $mime = mime_content_type($full) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($full));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function pdf(string $view, array $data, string $filename, string $paper = 'a4', string $orientation = 'portrait'): Response
    {
        $pdf = Pdf::loadView($view, $this->withOrganisation($data))
            ->setPaper($paper, $orientation);

        return $pdf->download($filename);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function stream(string $view, array $data, string $filename, string $paper = 'a4', string $orientation = 'portrait'): Response
    {
        $pdf = Pdf::loadView($view, $this->withOrganisation($data))
            ->setPaper($paper, $orientation);

        return $pdf->stream($filename);
    }

    public function recuRemboursement(Operation $operation, string $format = 'a4'): Response
    {
        $operation->loadMissing(['client', 'typeOperation', 'user']);
        $view = $format === 'ticket' ? 'documents.recu-ticket' : 'documents.recu-a4';
        $paper = $format === 'ticket' ? [0, 0, 226.77, 850] : 'a4';

        return $this->stream($view, ['operation' => $operation], 'recu-'.$operation->reference.'.pdf', $paper);
    }

    public function etatCompte(Client $client, Collection $operations, array $totaux): Response
    {
        return $this->stream('documents.etat-compte', [
            'client' => $client,
            'operations' => $operations,
            'totaux' => $totaux,
        ], 'etat-compte-'.$client->code_client.'.pdf');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function withOrganisation(array $data): array
    {
        return array_merge($data, [
            'organisation' => $this->organizationName(),
            'logo' => $this->logoDataUri(),
            'imprimeLe' => now()->format('d/m/Y H:i'),
            'money' => Money::class,
        ]);
    }
}
