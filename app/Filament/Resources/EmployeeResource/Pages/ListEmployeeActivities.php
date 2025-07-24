<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Illuminate\Database\Eloquent\Collection;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ListEmployeeActivities extends ListActivities
{
    protected static string $resource = EmployeeResource::class;

    protected ?string $heading = 'Historique du Personnel';

    public function getTitle(): string
    {
        return 'Historique du Personnel';
    }

    /**
     * @param  Collection  $fieldLabelMap
     */
    public function getFieldLabel(string $name): string
    {
        $customLabels = [
            'name' => 'Nom du Personnel',
            'updated_at' => 'Modifié le',
            'updated_by' => 'Modifié par',
        ];

        return $customLabels[$name] ?? parent::getFieldLabel($name);
    }
}
