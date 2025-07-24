<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ApprovalEnum: int implements HasColor, HasLabel
{
    case PENDING = 0;
    case APPROVED = 1;
    case POSTPONED = 2;
    case CANCELED = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::APPROVED => 'Approuvé',
            self::POSTPONED => 'Reporté',
            self::CANCELED => 'Annulé',
            default => 'Incconu',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING, self::POSTPONED => 'warning',
            self::APPROVED => 'success',
            self::CANCELED => 'danger',
        };
    }
}
