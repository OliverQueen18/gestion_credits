<?php

namespace App\Enums;

enum ClientStatut: int
{
    case Actif = 1;
    case Inactif = 0;

    public function label(): string
    {
        return match ($this) {
            self::Actif => 'Actif',
            self::Inactif => 'Inactif',
        };
    }
}
