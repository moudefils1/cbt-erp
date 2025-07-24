<?php

namespace App\Filament\Clusters\Sortie\Resources\EmployeeProductResource\Pages;

use App\Filament\Clusters\Sortie\Resources\EmployeeProductResource;
use App\Filament\Clusters\Sortie\Resources\EmployeeProductResource\RelationManagers\EmployeeProductItemsRelationManager;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewEmployeeProduct extends ViewRecord
{
    protected static string $resource = EmployeeProductResource::class;

    protected static ?string $title = 'Détails du Bon de Sortie';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make()
                ->hidden(fn ($record) => $record->employeeProductItems()->exists()),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            EmployeeProductItemsRelationManager::class,
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Card::make()
                    ->schema([
                        Infolists\Components\TextEntry::make('product_out_doc_number')
                            ->label('Numéro de Sortie')
                            ->badge()
                            ->color('warning'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Etat du Bon de Sortie')
                            ->badge()
                            ->color(fn ($record) => match ($record->status->value) {
                                0 => 'warning',
                                1 => 'success',
                            }),
                        Infolists\Components\TextEntry::make('createdBy.full_name')
                            ->label('Attribué par')
                            ->badge()
                            ->color('primary'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Date d\'Attribution')
                            ->date('d/m/Y H:i:s')
                            ->badge()
                            ->color('primary'),
                        Infolists\Components\TextEntry::make('updatedBy.full_name')
                            ->label('Dernière Modification')
                            ->badge()
                            ->color('success')
                            ->visible(fn (Model $record) => $record->updated_by != null),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Modifié le')
                            ->date('d/m/Y H:i:s')
                            ->badge()
                            ->color('success')
                            ->visible(fn (Model $record) => $record->updated_by != null),
                    ])->columns(2),
                Infolists\Components\Card::make()
                    ->schema([
                        Infolists\Components\TextEntry::make('description')
                            ->label('Description'),
                    ])
                    ->visible(fn (Model $record) => $record->description != null)
                    ->columnSpanFull(),
            ]);
    }
}
