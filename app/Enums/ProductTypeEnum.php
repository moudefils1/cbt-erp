<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProductTypeEnum: int implements HasLabel
{
    case Electronic = 1;
    case Vehicle = 2;
    case Other = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::Electronic => 'Matériel Électronique',
            self::Vehicle => 'Véhicule',
            self::Other => 'Vivres et Autres',
            default => 'Inconnu',
        };
    }
}
