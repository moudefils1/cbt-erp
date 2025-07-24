<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Enums\EmployeeStatusEnum;
use App\Enums\EmployeeTypeEnum;
use App\Filament\Resources\EmployeeResource;
use App\Filament\Resources\EmployeeResource\RelationManagers\EmployeeInternItemsRelationManager;
use App\Filament\Resources\EmployeeResource\RelationManagers\EmployeePositionsRelationManager;
use App\Filament\Resources\EmployeeResource\RelationManagers\EmployeeProductItemsRelationManager;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployee extends ViewRecord
{
    protected static string $resource = EmployeeResource::class;

    protected ?string $heading = 'Détails du Personnel';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make()
                ->hidden(fn ($record) => $record->employeeProductItems()->exists() || $record->internItems()->exists() || $record->employeePositions()->exists() || $record->leaves()->exists() || $record->debts()->exists() || $record->employeeLeaveBalances()->exists() || $record->trashed()),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            'employeeLeaveBalances' => EmployeeResource\RelationManagers\EmployeeLeaveBalancesRelationManager::class,
            'leaves' => EmployeeResource\RelationManagers\LeavesRelationManager::class,
            //'salaryBonuses' => EmployeeResource\RelationManagers\SalaryBonusesRelationManager::class,
            //'debts' => EmployeeResource\RelationManagers\DebtsRelationManager::class,
            //'debtItems' => EmployeeResource\RelationManagers\DebtItemsRelationManager::class,
            'employeeProductItems' => EmployeeProductItemsRelationManager::class,
            'employeePositions' => EmployeePositionsRelationManager::class,
            'internItems' => EmployeeInternItemsRelationManager::class,
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
                                Infolists\Components\TextEntry::make('surname')
                                    ->label('Prénoms'),
                                Infolists\Components\TextEntry::make('nationality')
                                    ->label('Nationalité'),
                                Infolists\Components\TextEntry::make('birth_place')
                                    ->label('Lieu de Naissance'),
                                Infolists\Components\TextEntry::make('birth_date')
                                    ->label('Date de Naissance')
                                    ->date('d/m/Y'),
                                Infolists\Components\TextEntry::make('nni')
                                    ->label('NNI'),
                                Infolists\Components\TextEntry::make('phone')
                                    ->label('Téléphone'),
                                Infolists\Components\TextEntry::make('email')
                                    ->label('Email'),
                                Infolists\Components\Fieldset::make('Etat Civil')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('gender')
                                            ->label('Genre')
                                            ->badge(),
                                        Infolists\Components\TextEntry::make('marital_status')
                                            ->label('Situation Matrimoniale')
                                            ->badge(),
                                        Infolists\Components\TextEntry::make('children_count')
                                            ->label('Nombre d\'Enfants')
                                            ->badge()
                                            ->color('info'),
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
                            ])->columns(3),
                        Infolists\Components\Fieldset::make('Informations Professionnelles')
                            ->schema([
                                Infolists\Components\TextEntry::make('location.name')
                                    ->label('Département'),
                                Infolists\Components\TextEntry::make('task.name')
                                    ->label('Poste'),
                                Infolists\Components\TextEntry::make('matricule')
                                    ->label('Matricule'),
                                Infolists\Components\TextEntry::make('cnps_no')
                                    ->label('Numéro CNPS'),

                                Infolists\Components\Fieldset::make('Type de Personnel')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('employee_type_id')
                                            ->label('Type de Personnel')
                                            ->badge()
                                            ->color('primary'),
                                        Infolists\Components\TextEntry::make('grade.name')
                                            ->label("Niveau d'Étude")
                                            ->badge()
                                            ->color('primary'),
                                        Infolists\Components\TextEntry::make('hiring_date')
                                            ->label('Début (Fonction/Contrat)')
                                            ->date('d/m/Y')
                                            ->badge()
                                            ->color('primary'),
                                        Infolists\Components\TextEntry::make('end_date')
                                            ->label('Fin (Fonction/Contrat)')
                                            ->date('d/m/Y')
                                            ->badge()
                                            ->color(fn ($record) => now()->isAfter($record->end_date) ? 'danger' : 'success')
                                            ->visible(fn ($record) => $record->employee_type_id->value == EmployeeTypeEnum::CDD->value),
                                        Infolists\Components\TextEntry::make('days_until_end')
                                            ->label('Contrat')
                                            ->getStateUsing(fn ($record) => now()->isAfter($record->end_date)
                                                ? 'Écoulé depuis '.now()->diffForHumans($record->end_date, true)
                                                : 'Reste: '.now()->diffForHumans($record->end_date, true))
                                            ->badge()
                                            ->color(fn ($record) => now()->isAfter($record->end_date) ? 'danger' : 'success')
                                            ->visible(fn ($record) => $record->employee_type_id->value == EmployeeTypeEnum::CDD->value),
                                    ])->columns(3),

//                                Infolists\Components\Fieldset::make('Grille de Salaire')
//                                    ->visible(fn () => auth()->user()->hasRole('super_admin') || auth()->user()->can('view_basic_salary'))
//                                    ->schema([
//                                        Infolists\Components\TextEntry::make('grid_category_id')
//                                            ->label('Catégorie')
//                                            ->badge()
//                                            ->color('info'),
//                                        Infolists\Components\TextEntry::make('echelon_id')
//                                            ->label('Échelon')
//                                            ->badge()
//                                            ->color('info'),
//                                        Infolists\Components\TextEntry::make('basic_salary')
//                                            ->label('Salaire de Base')
//                                            ->badge()
//                                            ->color('info'),
//                                    ])->columns(3),

                                Infolists\Components\Fieldset::make('Statut du Personnel')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('status')
                                            ->label('Actuellement')
                                            ->badge(),
                                        Infolists\Components\TextEntry::make('status_start_date')
                                            ->label('Date de Début de l\'Événement')
                                            ->date('d/m/Y')
                                            ->badge()
                                            ->color(function ($record) {
                                                return now()->isAfter($record->status_start_date) ? 'danger' : 'success';
                                            })
                                            ->visible(fn ($record) => $record->status_start_date != null && $record->status->value != EmployeeStatusEnum::WORKING->value),
                                        Infolists\Components\TextEntry::make('status_comment')
                                            ->label('Description')
                                            ->badge()
                                            ->color('danger')
                                            ->visible(fn ($record) => $record->status_comment != null && $record->status->value != EmployeeStatusEnum::WORKING->value),
                                    ])->columns(3),
                            ])->columns(3),

                        Infolists\Components\Fieldset::make('Personne à Contacter en Cas d\'Urgence')
                            ->schema([
                                Infolists\Components\TextEntry::make('emergency_contact_name')
                                    ->label('Nom'),
                                Infolists\Components\TextEntry::make('emergency_contact_phone')
                                    ->label('Téléphone'),
                                Infolists\Components\TextEntry::make('emergency_contact_relationship')
                                    ->label('Lien'),
                            ])->columns(3),
                    ]),
            ])->columns(1);
    }
}
