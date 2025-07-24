<?php

namespace App\Filament\Resources\GradeResource\Pages;

use App\Filament\Resources\GradeResource;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ListGardeActivities extends ListActivities
{
    protected static string $resource = GradeResource::class;

    protected ?string $heading = 'Historique du Niveau';

    public function getTitle(): string
    {
        return 'Historique du Niveau';
    }

    public function getFieldLabel(string $name): string
    {
        $customLabels = [
            'name' => 'Nom de Niveau',
            'updated_at' => 'Modifié le',
            'updated_by' => 'Modifié par',
        ];

        return $customLabels[$name] ?? parent::getFieldLabel($name);
    }
}
