<?php

namespace App\Filament\Resources\DebtResource\Pages;

use App\Filament\Resources\DebtResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDebts extends ViewRecord
{
    protected static string $resource = DebtResource::class;

    protected static ?string $title = 'Détails du Prêt';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            // ->hidden(fn ($record) => $record->items()->exists()),
        ];
    }

    //    public function getRelationManagers(): array
    //    {
    //        return [
    //            'items' => DebtResource\RelationManagers\ItemsRelationManager::class,
    //        ];
    //    }
}
