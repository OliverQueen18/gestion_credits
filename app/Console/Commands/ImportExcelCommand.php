<?php

namespace App\Console\Commands;

use App\Services\ExcelImportService;
use Illuminate\Console\Command;

class ImportExcelCommand extends Command
{
    protected $signature = 'import:excel {--execute : Importer réellement les données}';

    protected $description = 'Importe les trois fichiers Excel dans l’ordre (types, clients, opérations)';

    public function handle(ExcelImportService $import): int
    {
        $execute = (bool) $this->option('execute');

        if (! $execute) {
            $this->warn('Mode aperçu. Ajouter --execute pour écrire en base.');
        }

        $this->info('1/3 Types d’opérations');
        $types = $import->importTypeOperations($execute);
        $this->line("  {$types->importees}/{$types->lignes} · ignorées {$types->ignorees} · doublons {$types->doublons}");

        $this->info('2/3 Clients');
        $clients = $import->importClients($execute);
        $this->line("  {$clients->importees}/{$clients->lignes} · ignorées {$clients->ignorees} · doublons {$clients->doublons}");

        $this->info('3/3 Opérations');
        $operations = $import->importOperations($execute);
        $this->line("  {$operations->importees}/{$operations->lignes} · ignorées {$operations->ignorees} · doublons {$operations->doublons}");

        foreach ([...$types->erreurs, ...$clients->erreurs, ...$operations->erreurs] as $erreur) {
            $this->error($erreur);
        }

        return self::SUCCESS;
    }
}
