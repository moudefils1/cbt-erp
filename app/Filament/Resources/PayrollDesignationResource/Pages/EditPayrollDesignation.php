<?php

namespace App\Filament\Resources\PayrollDesignationResource\Pages;

use App\Filament\Resources\PayrollDesignationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayrollDesignation extends EditRecord
{
    protected static string $resource = PayrollDesignationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
