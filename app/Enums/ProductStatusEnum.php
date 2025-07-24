<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProductStatusEnum: int implements HasLabel
{
    case Reformed = 0;
    case Restored = 1;

    public function getLabel(): string
    {
        return match ($this) {
            self::Reformed => 'Réformé',
            self::Restored => 'Restitué',
            default => 'Inconnu',
        };
    }
}
