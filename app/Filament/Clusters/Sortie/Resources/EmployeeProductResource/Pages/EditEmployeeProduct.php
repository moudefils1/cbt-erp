<?php

namespace App\Filament\Clusters\Sortie\Resources\EmployeeProductResource\Pages;

use App\Filament\Clusters\Sortie\Resources\EmployeeProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeProduct extends EditRecord
{
    protected static string $resource = EmployeeProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(fn ($record) => $record->employeeProductItems()->exists()),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', [
            'record' => $this->record,
        ]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }
}
