<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentMethodEnum: int implements HasColor, HasIcon, HasLabel
{
    case BANK_TRANSFER = 1;
    case CASH = 2;
    case CHEQUE = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::BANK_TRANSFER => 'Virement Bancaire',
            self::CASH => 'Espèces',
            self::CHEQUE => 'Chèque',
            default => 'Inconnu',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::BANK_TRANSFER => 'blue',
            self::CASH => 'green',
            self::CHEQUE => 'yellow',
            default => null,
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::BANK_TRANSFER => 'primary',
            self::CASH => 'success',
            self::CHEQUE => 'warning',
            default => 'secondary',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::BANK_TRANSFER => 'heroicon-o-credit-card',
            self::CASH => 'heroicon-o-banknotes',
            self::CHEQUE => 'heroicon-o-document-check',
            default => null,
        };
    }
}
