<?php

namespace App\Filament\Resources\LeaveTypeResource\Pages;

use App\Filament\Resources\LeaveTypeResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewLeaveType extends ViewRecord
{
    protected static string $resource = LeaveTypeResource::class;

    protected static ?string $title = 'Détails du Type de Congé';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make()
                ->hidden(fn ($record) => $record->leaves()->exists() || $record->employeeLeaveBalances()->exists()),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            LeaveTypeResource\RelationManagers\EmployeeLeaveBalancesRelationManager::class,
            LeaveTypeResource\RelationManagers\LeavesRelationManager::class,
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
                                    ->label('Nom'),
                                Infolists\Components\TextEntry::make('days')
                                    ->label('Nombre de jours')
                                    ->badge()
                                    ->color('info'),
                                Infolists\Components\TextEntry::make('is_paid')
                                    ->label('Payé')
                                    ->badge()
                                    ->color(fn ($record) => $record->is_paid ? 'success' : 'danger')
                                    ->getStateUsing(fn ($record) => $record->is_paid ? 'Oui' : 'Non'),
                                Infolists\Components\Fieldset::make('Description')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('description')
                                            ->hiddenLabel()
                                            ->columnSpanFull(),
                                    ]),
                            ])->columns(3),
                        Infolists\Components\Fieldset::make('Dates')
                            ->schema([
                                Infolists\Components\TextEntry::make('createdBy.name')
                                    ->label('Créé par')
                                    ->badge()
                                    ->color('primary'),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Créé le')
                                    ->date('d/m/Y H:i')
                                    ->badge()
                                    ->color('primary'),
                                Infolists\Components\TextEntry::make('updatedBy.name')
                                    ->label('Modifié par')
                                    ->badge()
                                    ->color('success')
                                    ->visible(fn ($record) => $record->updated_by != null),
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Modifié le')
                                    ->date('d/m/Y H:i')
                                    ->badge()
                                    ->color('success')
                                    ->visible(fn ($record) => $record->updated_by != null),
                            ])->columns(4),
                    ]),
            ]);
    }
}
