<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MaritalStatusEnum: int implements HasColor, HasLabel
{
    case SINGLE = 0;
    case MARRIED = 1;
    case DIVORCED = 2;
    case WIDOWED = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::SINGLE => 'Célibataire',
            self::MARRIED => 'Marié(e)',
            self::DIVORCED => 'Divorcé(e)',
            self::WIDOWED => 'Veuf(ve)',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            default => 'info',
        };
    }
}
