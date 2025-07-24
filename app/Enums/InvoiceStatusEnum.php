<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum InvoiceStatusEnum: int implements HasLabel
{
    case INCOMPLETE = 0;
    case COMPLETE = 1;

    public function getLabel(): string
    {
        return match ($this) {
            self::INCOMPLETE => 'Incomplete',
            self::COMPLETE => 'Complete',
            default => 'Inconnu',
        };
    }
}
