<?php

namespace App\Filament\Resources\SalaryDeductionResource\Pages;

use App\Filament\Resources\SalaryDeductionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSalaryDeductions extends ListRecords
{
    protected static string $resource = SalaryDeductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalHeading('Ajouter un prélevément de salaire')
                ->mutateFormDataUsing(function (array $data) {
                    $data['created_by'] = auth()->id();

                    return $data;
                }),
        ];
    }
}
