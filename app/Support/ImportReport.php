<?php

namespace App\Support;

class ImportReport
{
    public int $lignes = 0;

    public int $importees = 0;

    public int $ignorees = 0;

    public int $doublons = 0;

    /** @var list<string> */
    public array $erreurs = [];

    /** @var list<string> */
    public array $avertissements = [];

    public function addErreur(string $message): void
    {
        $this->erreurs[] = $message;
        $this->ignorees++;
    }

    public function addAvertissement(string $message): void
    {
        $this->avertissements[] = $message;
    }

    public function toArray(): array
    {
        return [
            'lignes' => $this->lignes,
            'importees' => $this->importees,
            'ignorees' => $this->ignorees,
            'doublons' => $this->doublons,
            'erreurs' => $this->erreurs,
            'avertissements' => $this->avertissements,
        ];
    }
}
