<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StateEnum: int implements HasColor, HasLabel
{
    case IN_PROGRESS = 1;
    case COMPLETED = 2;
    case STANDBY = 3;
    // case CANCELED = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::IN_PROGRESS => 'En cours',
            self::COMPLETED => 'Terminé',
            self::STANDBY => 'En attente',
            // self::CANCELED => 'Annulé',
            default => 'Inconnu',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::IN_PROGRESS => 'info',
            self::COMPLETED => 'success',
            self::STANDBY => 'warning',
            // self::CANCELED => 'danger',
            default => 'gray',
        };
    }

    public function is($status): bool
    {
        return $this === $status;
    }

    public function isNot($status): bool
    {
        return $this !== $status;
    }
}
