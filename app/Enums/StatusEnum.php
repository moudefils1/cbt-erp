<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum StatusEnum: int implements HasColor, HasIcon, HasLabel
{
    case ACTIVE = 1;
    case INACTIVE = 0;

    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVE => 'Actif',
            self::INACTIVE => 'Passif',
            default => 'Inconnu',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::INACTIVE => 'danger',
            default => 'gray',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::INACTIVE => 'danger',
            default => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::ACTIVE => 'heroicon-o-check-circle',
            self::INACTIVE => 'heroicon-o-x-circle',
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
