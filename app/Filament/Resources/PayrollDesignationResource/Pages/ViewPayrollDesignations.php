<?php

namespace App\Filament\Resources\PayrollDesignationResource\Pages;

use App\Filament\Resources\PayrollDesignationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPayrollDesignations extends ViewRecord
{
    protected static string $resource = PayrollDesignationResource::class;

    protected static ?string $title = 'Détails de la Désignation';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
