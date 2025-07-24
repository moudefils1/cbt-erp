<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PartEnum: int implements HasLabel
{
    case EMPLOYEE = 1;
    case EMPLOYER = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::EMPLOYEE => 'Part salariale',
            self::EMPLOYER => 'Part patronale',
            default => 'Incconu',
        };
    }

    //    public function getColor(): string
    //    {
    //        return match ($this) {
    //
    //        };
    //    }
}
