<?php

namespace App\Filament\Resources\HolidayResource\Pages;

use App\Filament\Resources\HolidayResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHolidays extends ListRecords
{
    protected static string $resource = HolidayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalHeading('Ajouter un jour férié')
                ->mutateFormDataUsing(function (array $data) {
                    $data['created_by'] = auth()->id();

                    return $data;
                }),
        ];
    }
}
