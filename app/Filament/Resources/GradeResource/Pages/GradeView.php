<?php

namespace App\Filament\Resources\GradeResource\Pages;

use App\Filament\Resources\GradeResource;
use App\Filament\Resources\GradeResource\RelationManagers\GradeEmployeesRelationManager;
use App\Filament\Resources\GradeResource\RelationManagers\GradeInternsRelationManager;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class GradeView extends ViewRecord
{
    protected static string $resource = GradeResource::class;

    protected static ?string $title = 'Informations du Niveau';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make()
                ->hidden(fn ($record) => $record->interns()->exists() || $record->employees()->exists()),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            GradeInternsRelationManager::class,
            GradeEmployeesRelationManager::class,
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Card::make()
                    ->schema([
                        Infolists\Components\Fieldset::make('Informations Générales')
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Nom du Niveau'),
                                Infolists\Components\TextEntry::make('description')
                                    ->label('Description'),

                            ]),
                    ]),
            ]);
    }
}
