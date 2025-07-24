<?php

namespace App\Filament\Resources\PayrollDesignationResource\Pages;

use App\Filament\Resources\PayrollDesignationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayrollDesignations extends ListRecords
{
    protected static string $resource = PayrollDesignationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['created_by'] = auth()->id();

                    return $data;
                }),
        ];
    }
}
