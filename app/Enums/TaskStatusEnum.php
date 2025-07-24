<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TaskStatusEnum: int implements HasLabel
{
    case UNAVAILABLE = 0;
    case AVAILABLE = 1;

    public function getLabel(): string
    {
        return match ($this) {
            self::UNAVAILABLE => 'Occupée',
            self::AVAILABLE => 'Disponible',
            default => 'Inconnu',
        };
    }
}
