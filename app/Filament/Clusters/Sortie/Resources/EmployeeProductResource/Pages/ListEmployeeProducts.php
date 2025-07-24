<?php

namespace App\Filament\Clusters\Sortie\Resources\EmployeeProductResource\Pages;

use App\Filament\Clusters\Sortie\Resources\EmployeeProductResource;
use App\Models\EmployeeProduct;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeProducts extends ListRecords
{
    protected static string $resource = EmployeeProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /*public function getTabs(): array
    {
        $tabs = [];

        $tabs['all'] = Tab::make('Total')
            ->badge(EmployeeProduct::count());

        return $tabs;
    }*/
}
