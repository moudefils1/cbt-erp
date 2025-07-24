<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use App\Enums\EmployeeStatusEnum;
use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EmployeeLeaveBalancesRelationManager extends RelationManager
{
    protected static string $relationship = 'employeeLeaveBalances';

    protected static ?string $title = 'Congés Acquis';

    protected static ?string $icon = 'heroicon-o-cog-8-tooth';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('Détails du Congé Acquis')
                    ->schema([
                        Forms\Components\Select::make('leave_type_id')
                            ->label('Type de congé')
                            ->placeholder('Sélectionnez un type de congé')
                            ->options(function ($get) {
                                return \App\Models\LeaveType::query()
                                    ->get()
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('year', null))
                            ->validationMessages([
                                'required' => 'Veuillez sélectionner un type de congé.',
                            ])
                            ->when(auth()->user()->hasRole('super_admin') || auth()->user()->can('create_custom_leave_type', LeaveType::class), fn ($select) => $select->createOptionForm(function ($form) {
                                $form
                                    ->schema([
                                        Forms\Components\Section::make()
                                            ->schema([
                                                Forms\Components\Fieldset::make('Type de Congé')
                                                    ->schema([
                                                        Forms\Components\TextInput::make('name')
                                                            ->label('Nom')
                                                            ->placeholder('Nom du type de congé')
                                                            ->validationMessages([
                                                                'required' => 'Champ obligatoire',
                                                            ])
                                                            ->columnSpanFull()
                                                            ->required(),
                                                        Forms\Components\Textarea::make('description')
                                                            ->label('Description')
                                                            ->placeholder('Description du type de congé')
                                                            ->columnSpanFull(),
                                                        Forms\Components\Toggle::make('is_paid')
                                                            ->label('Payé')
                                                            ->default(false)
                                                            ->onColor('success')
                                                            ->offColor('danger')
                                                            ->helperText('Est-ce que ce congé est payé ?'),
                                                    ]),
                                            ]),
                                    ]);

                                return $form->model(\App\Models\LeaveType::class);
                            })->createOptionUsing(function ($data) {
                                $leaveType = \App\Models\LeaveType::create([
                                    'name' => $data['name'],
                                    'description' => $data['description'],
                                    'is_paid' => $data['is_paid'],
                                    'created_by' => auth()->id(),
                                ]);

                                return $leaveType->id;
                            })
                            )
                            ->required(),
                        Forms\Components\Select::make('year')
                            ->label('Année')
                            ->placeholder('Sélectionnez une année')
                            ->options(function ($get) {
                                $employee = $this->getOwnerRecord(); // Récupérer l'employé actuel (owner)
                                $employeeId = $employee ? $employee->id : null; // ID de l'employé
                                $leaveTypeId = $get('leave_type_id'); // Récupérer l'ID du type de congé

                                if (! $employeeId || ! $leaveTypeId) {
                                    return [date('Y') => date('Y')];  // Si l'employé ou le type de congé n'est pas défini, retourner l'année actuelle
                                }

                                // Trouver la dernière année de congé pour cet employé et ce type de congé
                                $lastYear = EmployeeLeaveBalance::where('employee_id', $employeeId)
                                    ->where('leave_type_id', $leaveTypeId)
                                    ->whereNull('deleted_at')
                                    ->max('year');

                                // Si aucune donnée n'existe, retourner l'année actuelle. Sinon, retourner l'année suivante
                                $nextYear = $lastYear ? $lastYear + 1 : date('Y');

                                return [$nextYear => $nextYear];  // Retourner l'année suivante ou l'année actuelle si aucun enregistrement n'existe
                            })
                            ->default(function ($get) {
                                $employee = $this->getOwnerRecord(); // Récupérer l'employé actuel (owner)
                                $employeeId = $employee ? $employee->id : null; // ID de l'employé
                                $leaveTypeId = $get('leave_type_id'); // Récupérer l'ID du type de congé

                                if (! $employeeId || ! $leaveTypeId) {
                                    return date('Y');  // Si l'employé ou le type de congé n'est pas défini, retourner l'année actuelle
                                }

                                // Trouver la dernière année de congé pour cet employé et ce type de congé
                                $lastYear = EmployeeLeaveBalance::where('employee_id', $employeeId)
                                    ->where('leave_type_id', $leaveTypeId)
                                    ->whereNull('deleted_at')
                                    ->max('year');

                                // Retourner l'année suivante si un enregistrement existe, sinon l'année actuelle
                                return $lastYear ? $lastYear + 1 : date('Y');
                            })
                            ->validationMessages([
                                'required' => 'Veuillez sélectionner une année.',
                            ])
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('total_days')
                            ->label('Nombre de jours acquis')
                            ->placeholder('Nombre de jours acquis pour ce type de congé')
                            ->numeric()
                            ->minValue(1)
                            ->validationMessages([
                                'required' => 'Veuillez saisir le nombre de jours.',
                                'numeric' => 'Le nombre de jours doit être un nombre.',
                                'min' => 'Le nombre de jours doit être supérieur à 0.',
                            ])
                            ->required(),
                        Forms\Components\Placeholder::make('remaining_days')
                            ->label('Nombre des congés restants')
                            ->content(function (Forms\Get $get) {
                                $leaveTypeId = $get('leave_type_id'); // Récupérer l'ID du type de congé

                                if (! $leaveTypeId) {
                                    return 'Aucun congé disponible';  // Si aucun type de congé sélectionné, afficher un message
                                }

                                // Récupérer le nombre total de jours acquis pour ce type de congé
                                $leaveBalance = EmployeeLeaveBalance::where('leave_type_id', $leaveTypeId)
                                    ->whereNull('deleted_at')
                                    ->latest()
                                    ->first();

                                // Si aucune donnée n'existe pour le type de congé sélectionné, afficher un message
                                if (! $leaveBalance) {
                                    return 'Aucun congé disponible';
                                }

                                return $leaveBalance->total_days - $leaveBalance->used_days;  // Retourner le nombre de jours restants
                            }),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('leaveType.name')
                    ->label('Type de congé')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_days')
                    ->label('Nombre Acquis (Jours)')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('used_days')
                    ->label('Nombre Utilisés (Jours)')
                    ->badge()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('year')
                    ->label('Année')
                    ->sortable(),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->visible(fn () => auth()->user()->hasRole('super_admin'))
                    ->label('Créé par')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->date('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updatedBy.name')
                    ->label('Modifié par')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->getStateUsing(fn ($record) => $record->updated_by ? $record->updated_at : null)
                    ->label('Modifié le')
                    ->date('d/m/Y H:i')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->visible(fn () => auth()->user()->hasRole('super_admin') || $this->ownerRecord->status == EmployeeStatusEnum::WORKING || $this->ownerRecord->status == EmployeeStatusEnum::ON_LEAVE)
                    ->modalHeading('Ajouter un Congé Acquis')
                    ->mutateFormDataUsing(function (array $data) {
                        $data['created_by'] = auth()->id();

                        $employeeBalance = EmployeeLeaveBalance::where('leave_type_id', $data['leave_type_id'])
                            ->where('employee_id', $this->getOwnerRecord()->id)
                            ->whereNull('deleted_at')
                            ->latest('year')
                            ->first();

                        $totalRemainingDays = $employeeBalance?->total_days - $employeeBalance?->used_days;

                        $data['total_days'] = $data['total_days'] + ($totalRemainingDays);

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(fn ($record) => $record->leaves()->exists()
                        || EmployeeLeaveBalance::where('employee_id', $record->employee_id)
                            ->where('leave_type_id', $record->leave_type_id)
                            ->where('year', '>', $record->year)
                            ->exists()
                        //                        || $this->ownerRecord->status != EmployeeStatusEnum::WORKING
                        //                        || $this->ownerRecord->status != EmployeeStatusEnum::ON_LEAVE
                    )
                    ->modalHeading('Modifier un Congé Acquis')
                    ->mutateFormDataUsing(function (array $data) {
                        $data['updated_by'] = auth()->id();

                        return $data;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn ($record) => $record->leaves()->exists()
                        || EmployeeLeaveBalance::where('employee_id', $record->employee_id)
                            ->where('leave_type_id', $record->leave_type_id)
                            ->where('year', '>', $record->year)
                            ->exists()
//                        || $this->ownerRecord->status != EmployeeStatusEnum::WORKING
//                        || $this->ownerRecord->status != EmployeeStatusEnum::ON_LEAVE
                        || $record->trashed()
                    )
                    ->modalHeading('Supprimer un Congé Acquis'),
            ])
            ->bulkActions([
                //
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
