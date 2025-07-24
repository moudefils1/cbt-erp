<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum GridCategoryEnum: int implements HasLabel
{
    case NB1 = 1;
    case NB2 = 2;
    case B = 3;
    case C = 4;
    case D = 5;
    case E = 6;
    case F = 7;
    case G = 8;
    case H = 9;

    public function getLabel(): string
    {
        return match ($this) {
            self::NB1 => 'NB1 (non bancaires)',
            self::NB2 => 'NB2 (non bancaires)',
            self::B => 'B (Employés de banque)',
            self::C => 'C (Employés de banque)',
            self::D => 'D (Employés de banque)',
            self::E => 'E (Gradés de banque)',
            self::F => 'F (Gradés de banque)',
            self::G => 'G (Cadres de banque)',
            self::H => 'H (Cadres supérieurs de banque)',
            default => 'Inconnu',
        };
    }
}
