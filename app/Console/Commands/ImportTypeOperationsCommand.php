<?php

namespace App\Console\Commands;

use App\Services\ExcelImportService;
use Illuminate\Console\Command;

class ImportTypeOperationsCommand extends Command
{
    protected $signature = 'import:type-operations {--execute : Importer réellement les données}';

    protected $description = 'Importe BD/TYPE OPERATION.xlsx après validation';

    public function handle(ExcelImportService $import): int
    {
        $execute = (bool) $this->option('execute');
        $this->info($execute ? 'Import réel…' : 'Aperçu (dry-run). Relancer avec --execute pour importer.');

        $report = $import->importTypeOperations($execute);
        $this->renderReport($report->toArray());

        return $report->erreurs === [] ? self::SUCCESS : self::FAILURE;
    }

    private function renderReport(array $report): void
    {
        $this->table(
            ['Lignes', 'Importées', 'Ignorées', 'Doublons', 'Erreurs'],
            [[$report['lignes'], $report['importees'], $report['ignorees'], $report['doublons'], count($report['erreurs'])]]
        );

        foreach ($report['erreurs'] as $erreur) {
            $this->error($erreur);
        }
        foreach ($report['avertissements'] as $avertissement) {
            $this->warn($avertissement);
        }
    }
}
