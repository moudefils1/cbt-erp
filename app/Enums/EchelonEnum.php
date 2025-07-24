<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EchelonEnum: int implements HasLabel
{
    case Echelon1 = 1;
    case Echelon2 = 2;
    case Echelon3 = 3;
    case Echelon4 = 4;
    case Echelon5 = 5;

    public function getLabel(): string
    {
        return match ($this) {
            self::Echelon1 => 'Echelon 1',
            self::Echelon2 => 'Echelon 2',
            self::Echelon3 => 'Echelon 3',
            self::Echelon4 => 'Echelon 4',
            self::Echelon5 => 'Echelon 5',
            default => 'Inconnu',
        };
    }
}
