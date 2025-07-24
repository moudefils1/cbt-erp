<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AbsenceStatusEnum: int implements HasColor, HasLabel
{
    case PRESENT = 1;
    case ABSENT = 2;
    case VACATION = 3;
    case FORMATION = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::PRESENT => 'Présent',
            self::ABSENT => 'Absent',
            self::VACATION => 'Congé',
            self::FORMATION => 'Formation',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PRESENT => 'success',
            self::ABSENT => 'danger',
            self::VACATION => 'warning',
            self::FORMATION => 'info',
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
