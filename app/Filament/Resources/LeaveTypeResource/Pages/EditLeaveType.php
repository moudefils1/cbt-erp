<?php

namespace App\Filament\Resources\LeaveTypeResource\Pages;

use App\Filament\Resources\LeaveTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLeaveType extends EditRecord
{
    protected static string $resource = LeaveTypeResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', [
            'record' => $this->record,
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(fn ($record) => $record->leaves()->exists() || $record->employeeLeaveBalances()->exists()),
        ];
    }
}
