<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EmployeeTypeEnum: int implements HasLabel
{
    case CDD = 1;
    case CDI = 2;
    case FONCTIONNAIRE = 3;
    case STATE_CONTRACT_EMPLOYEE = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::CDD => 'Contrat à Durée Déterminée (CDD)',
            self::CDI => 'Contrat à Durée Indéterminée (CDI)',
            self::FONCTIONNAIRE => 'Fonctionnaire de l\'État',
            self::STATE_CONTRACT_EMPLOYEE => 'Contractuel de l\'État',
            default => 'Inconnu',
        };
    }
}
