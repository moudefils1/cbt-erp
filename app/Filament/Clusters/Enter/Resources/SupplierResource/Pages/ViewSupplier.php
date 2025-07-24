<?php

namespace App\Filament\Clusters\Enter\Resources\SupplierResource\Pages;

use App\Filament\Clusters\Enter\Resources\SupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSupplier extends ViewRecord
{
    protected static ?string $title = 'Détails du Fournisseur';

    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make()
                ->hidden(fn ($record) => $record->invoices()->exists()),
        ];
    }
}
