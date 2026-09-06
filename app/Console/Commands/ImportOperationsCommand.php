<?php

namespace App\Console\Commands;

use App\Services\ExcelImportService;
use Illuminate\Console\Command;

class ImportOperationsCommand extends Command
{
    protected $signature = 'import:operations {--execute : Importer réellement les données}';

    protected $description = 'Importe BD/OPERATIONS.xlsx après validation';

    public function handle(ExcelImportService $import): int
    {
        $execute = (bool) $this->option('execute');
        $this->info($execute ? 'Import réel…' : 'Aperçu (dry-run). Relancer avec --execute pour importer.');

        $report = $import->importOperations($execute);
        $this->table(
            ['Lignes', 'Importées', 'Ignorées', 'Doublons', 'Erreurs'],
            [[$report->lignes, $report->importees, $report->ignorees, $report->doublons, count($report->erreurs)]]
        );

        foreach ($report->erreurs as $erreur) {
            $this->error($erreur);
        }
        foreach ($report->avertissements as $avertissement) {
            $this->warn($avertissement);
        }

        return self::SUCCESS;
    }
}
