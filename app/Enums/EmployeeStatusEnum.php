<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EmployeeStatusEnum: int implements HasColor, HasLabel
{
    case WORKING = 1;
    case CONTRACT_ENDED = 2;
    case RESIGNED = 3;
    case RETIRED = 4;
    case FIRED = 5;
    case DECEASED = 6;
    case ON_LEAVE = 7;
    case IN_TRAINING = 8;

    public function getLabel(): string
    {
        return match ($this) {
            self::WORKING => 'En Service',
            self::CONTRACT_ENDED => 'Contrat Terminé',
            self::RESIGNED => 'Démissionné',
            self::RETIRED => 'Retraité',
            self::FIRED => 'Licencié',
            self::DECEASED => 'Décédé',
            self::ON_LEAVE => 'En Congé',
            self::IN_TRAINING => 'En Formation',
            default => 'Inconnu',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::WORKING => 'success',
            self::CONTRACT_ENDED => 'primary',
            self::ON_LEAVE, self::IN_TRAINING => 'warning',
            self::RETIRED => 'info',
            self::RESIGNED, self::FIRED => 'danger',
            self::DECEASED => 'gray',
            default => 'secondary',
        };
    }
}
