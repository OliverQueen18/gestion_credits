<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Gestionnaire = 'gestionnaire';
    case Consultation = 'consultation';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrateur',
            self::Gestionnaire => 'Gestionnaire',
            self::Consultation => 'Consultation',
        };
    }

    public function canPerformFinancialOperations(): bool
    {
        return $this === self::Admin || $this === self::Gestionnaire;
    }

    public function canManageAdministration(): bool
    {
        return $this === self::Admin;
    }
}
