<?php

namespace App\Enums;

enum OperationSens: int
{
    case Credit = 1;
    case Remboursement = 2;

    public function label(): string
    {
        return match ($this) {
            self::Credit => 'Crédit',
            self::Remboursement => 'Remboursement',
        };
    }

    public function incrementeEncours(): bool
    {
        return $this === self::Credit;
    }
}
