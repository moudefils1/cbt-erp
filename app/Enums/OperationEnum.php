<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum OperationEnum: int implements HasLabel
{
    case TOTAL_BRUT_IMPOSABLE = 1;
    case TOTAL_BRUT_NON_IMPOSABLE = 2;
    case TOTAL_RETENUE_OBLIGATOIRE = 3;
    case TOTAL_RETENUE_PERSONNELLES = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::TOTAL_BRUT_IMPOSABLE => 'Total Brut Imposable',
            self::TOTAL_BRUT_NON_IMPOSABLE => 'Total Brut non Imposable',
            self::TOTAL_RETENUE_OBLIGATOIRE => 'Total Retenue Obligatoire',
            self::TOTAL_RETENUE_PERSONNELLES => 'Total Retenue Personnelles',
            default => 'Incconu',
        };
    }

    //    public function getColor(): string
    //    {
    //        return match ($this) {
    //
    //        };
    //    }
}
