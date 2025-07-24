<?php

namespace App\Filament\Clusters\Sortie\Resources\EmployeeProductItemResource\Pages;

use App\Filament\Clusters\Sortie\Resources\EmployeeProductItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeProductItems extends ListRecords
{
    protected static string $resource = EmployeeProductItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
