<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum GenderEnum: int implements HasColor, HasLabel
{
    case HOMME = 1;
    case FEMME = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::HOMME => 'Homme',
            self::FEMME => 'Femme',
            default => 'Inconnu',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::HOMME => 'info',
            self::FEMME => 'danger',
            default => 'gray',
        };
    }
}
