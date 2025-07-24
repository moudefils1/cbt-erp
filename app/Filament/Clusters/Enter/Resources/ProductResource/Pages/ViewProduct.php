<?php

namespace App\Filament\Clusters\Enter\Resources\ProductResource\Pages;

use App\Filament\Clusters\Enter\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Détails du Produit';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make()
                ->hidden(fn ($record) => $record->employeeProductItems()->exists()),
        ];
    }
}
