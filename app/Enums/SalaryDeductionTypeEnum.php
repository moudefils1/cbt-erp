<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum SalaryDeductionTypeEnum: int implements HasColor, HasIcon, HasLabel
{
    case PERCENTAGE = 1;
    case NORMAL = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::PERCENTAGE => 'Pourcentage',
            self::NORMAL => 'Normal',
            default => 'Inconnu',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PERCENTAGE => 'success',
            self::NORMAL => 'warning',
            default => 'gray',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::PERCENTAGE => 'success',
            self::NORMAL => 'warning',
            default => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PERCENTAGE => 'heroicon-o-percent-badge',
            self::NORMAL => 'heroicon-o-currency-dollar',
            default => null,
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
