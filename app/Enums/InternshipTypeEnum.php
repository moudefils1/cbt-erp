<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum InternshipTypeEnum: int implements HasLabel
{
    case academic_internship = 1;
    case professional_internship = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::academic_internship => 'Stage Académique',
            self::professional_internship => 'Stage Professionnel',
            default => 'Unknown',
        };
    }
}
