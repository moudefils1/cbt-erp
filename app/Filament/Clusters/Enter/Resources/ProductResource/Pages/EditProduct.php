<?php

namespace App\Filament\Clusters\Enter\Resources\ProductResource\Pages;

use App\Filament\Clusters\Enter\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(fn ($record) => $record->employeeProductItems()->exists()),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        return static::getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }
}
