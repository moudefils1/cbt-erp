<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ListTaskActivities extends ListActivities
{
    protected static string $resource = TaskResource::class;

    protected ?string $heading = 'Historique du Poste';

    public function getTitle(): string
    {
        return $this->heading;
    }

    public function getFieldLabel(string $name): string
    {
        $customLabels = [
            'name' => 'Nom du Poste',
            'updated_at' => 'Modifié le',
            'updated_by' => 'Modifié par',
        ];

        return $customLabels[$name] ?? parent::getFieldLabel($name);
    }
}
