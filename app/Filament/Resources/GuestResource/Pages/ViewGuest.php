<?php

namespace App\Filament\Resources\GuestResource\Pages;

use App\Filament\Resources\GuestResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewGuest extends ViewRecord
{
    protected static string $resource = GuestResource::class;

    protected ?string $heading = 'Détails de l\'invité';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make()
                ->hidden(fn ($record) => $record->guestItems()->exists()),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            GuestResource\RelationManagers\GuestItemsRelationManager::class,
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
                                Infolists\Components\TextEntry::make('title')
                                    ->label('Titre'),
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Nom'),
                                Infolists\Components\TextEntry::make('city')
                                    ->label('Ville'),
                                Infolists\Components\TextEntry::make('gender')
                                    ->label('Genre')
                                    ->badge(),
                                Infolists\Components\Fieldset::make('Contact')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('phone')
                                            ->label('Téléphone'),
                                        Infolists\Components\TextEntry::make('email')
                                            ->label('Email'),
                                        Infolists\Components\Fieldset::make('Adresse')
                                            ->label('Addresse de l\'invité')
                                            ->schema([
                                                Infolists\Components\TextEntry::make('address')
                                                    ->hiddenLabel(),
                                            ])->columns(1),
                                    ])->columns(2),
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
                            ])->columns(4),
                        Infolists\Components\Fieldset::make('Institution')
                            ->schema([
                                Infolists\Components\TextEntry::make('company')
                                    ->label("Nom de l'Institution"),
                                Infolists\Components\TextEntry::make('company_phone')
                                    ->label('Téléphone de l\'Institution'),
                                Infolists\Components\Fieldset::make('company_address')
                                    ->label('Adresse de l\'Institution')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('company_address')
                                            ->hiddenLabel(),
                                    ])->columns(1),
                            ])->columns(2),
                    ]),
            ]);
    }
}
