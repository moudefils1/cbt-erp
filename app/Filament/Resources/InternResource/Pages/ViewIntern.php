<?php

namespace App\Filament\Resources\InternResource\Pages;

use App\Enums\StateEnum;
use App\Filament\Resources\InternResource;
use App\Filament\Resources\InternResource\RelationManagers\InternItemsRelationManager;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewIntern extends ViewRecord
{
    protected static string $resource = InternResource::class;

    protected static ?string $title = 'Informations du Stagiaire';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('terminateInternship')
                ->visible(fn ($record) => $record->status->value == StateEnum::IN_PROGRESS->value && $record->internship_end_date > now()->format('Y-m-d') && auth()->user()?->can('terminate_internship'))
                ->label('Terminer le Stage')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Terminer le Stage')
                ->modalDescription('Êtes-vous sûr de vouloir terminer le stage de ce stagiaire ?')
                ->modalIcon('heroicon-o-user-plus')
                ->action(function ($record) {
                    $record->update(['status' => StateEnum::COMPLETED]);
                    $record->internItems()->update(['status' => StateEnum::COMPLETED]);
                })
                ->successNotificationTitle('Le stage a été terminé avec succès'),
            Actions\EditAction::make(),
            Actions\DeleteAction::make()
                ->hidden(fn ($record) => $record->status->value == StateEnum::COMPLETED->value || $record->internItems()->exists()),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            InternItemsRelationManager::class,
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
                                    ->label('Nom et Prénom'),
                                Infolists\Components\TextEntry::make('gender')
                                    ->label('Genre')
                                    ->badge(),
                                Infolists\Components\TextEntry::make('phone')
                                    ->label('Téléphone'),
                                Infolists\Components\TextEntry::make('email')
                                    ->label('E-mail'),
                                Infolists\Components\TextEntry::make('address')
                                    ->label('Adresse'),
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
                        Infolists\Components\Fieldset::make('Informations Académiques')
                            ->schema([
                                Infolists\Components\TextEntry::make('university')
                                    ->label('Université'),
                                Infolists\Components\TextEntry::make('department')
                                    ->label('Filière'),
                                Infolists\Components\TextEntry::make('grade.name')
                                    ->label('Niveau d\'Etude')
                                    ->badge()
                                    ->color('info'),
                            ])->columns(3),
                        Infolists\Components\Fieldset::make('Informations sur le Stage')
                            ->schema([
                                Infolists\Components\TextEntry::make('internship_type')
                                    ->label('Type de Stage'),
                                Infolists\Components\TextEntry::make('internship_start_date')
                                    ->label('Début de Stage')
                                    ->badge()
                                    ->color(fn ($record) => match ($record->status) {
                                        StateEnum::COMPLETED => 'success',
                                        StateEnum::IN_PROGRESS => 'info',
                                        StateEnum::STANDBY => 'warning',
                                        default => 'gray',
                                    })
                                    ->date('d/m/Y'),
                                Infolists\Components\TextEntry::make('internship_end_date')
                                    ->label('Fin de Stage')
                                    ->badge()
                                    ->color(fn ($record) => match ($record->status) {
                                        StateEnum::COMPLETED => 'success',
                                        default => 'gray',
                                    })
                                    ->date('d/m/Y'),
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Statut du Stage')
                                    ->badge(),
                                Infolists\Components\TextEntry::make('internship_duration')
                                    ->label('Durée du Stage (en jours)')
                                    ->badge()
                                    ->color('info'),
                            ])->columns(4),
                    ]),
            ]);
    }
}
