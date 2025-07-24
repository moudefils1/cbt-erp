<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LeaveEnum: int implements HasColor, HasLabel
{
    case PENDING = 0;
    case APPROVED = 1;
    case REJECTED = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::APPROVED => 'Approuvé',
            self::REJECTED => 'Rejeté',
            default => 'Incconu',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
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
