<?php

namespace App\Filament\Resources\SalaryDeductionResource\Pages;

use App\Filament\Resources\SalaryDeductionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSalaryDeduction extends EditRecord
{
    protected static string $resource = SalaryDeductionResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', [
            'record' => $this->record,
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
