<?php

namespace App\Filament\Resources\LocationResource\Pages;

use App\Filament\Resources\LocationResource;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ListLocationActivities extends ListActivities
{
    protected static string $resource = LocationResource::class;

    public function getFieldLabel(string $name): string
    {
        $customLabels = [
            'name' => 'Nom',
            'updated_at' => 'Modifié le',
            'updated_by' => 'Modifié par',
        ];

        return $customLabels[$name] ?? parent::getFieldLabel($name);
    }
}
