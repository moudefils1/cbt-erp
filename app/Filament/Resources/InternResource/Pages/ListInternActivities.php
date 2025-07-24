<?php

namespace App\Filament\Resources\InternResource\Pages;

use App\Filament\Resources\InternResource;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ListInternActivities extends ListActivities
{
    protected static string $resource = InternResource::class;

    protected ?string $heading = 'Historique de Stagiaire';

    public function getTitle(): string
    {
        return 'Historique de Stagiaire';
    }

    public function getFieldLabel(string $name): string
    {
        $customLabels = [
            'name' => 'Nom du Stagiaire',
            'updated_at' => 'Modifié le',
            'updated_by' => 'Modifié par',
        ];

        return $customLabels[$name] ?? parent::getFieldLabel($name);
    }
}
