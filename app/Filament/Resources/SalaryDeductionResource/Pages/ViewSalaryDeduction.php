<?php

namespace App\Filament\Resources\SalaryDeductionResource\Pages;

use App\Filament\Resources\SalaryDeductionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSalaryDeduction extends ViewRecord
{
    protected static string $resource = SalaryDeductionResource::class;

    protected static ?string $title = 'Détails du Prélevé de Salaire';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->modalHeading('Modifier le Prélevément de Salaire')
                ->mutateFormDataUsing(function (array $data) {
                    $data['updated_by'] = auth()->id();

                    return $data;
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
