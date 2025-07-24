<?php

namespace App\Filament\Resources\LeaveTypeResource\RelationManagers;

use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EmployeeLeaveBalancesRelationManager extends RelationManager
{
    protected static string $relationship = 'employeeLeaveBalances';

    protected static ?string $title = 'Congés Acquis du Personnel';

    protected static ?string $icon = 'heroicon-o-cog-8-tooth';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('Détails du Congé Acquis')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Personnel')
                            ->placeholder('Sélectionnez un personnel')
                            ->options(function () {
                                return Employee::all()->pluck('full_name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('year', null))
                            ->validationMessages([
                                'required' => 'Veuillez sélectionner un personnel.',
                            ])
                            ->required(),
                        Forms\Components\Select::make('year')
                            ->label('Année')
                            ->placeholder('Sélectionnez une année')
                            ->options(function ($get) {
                                $employeeId = $get('employee_id');
                                $leaveTypeId = $this->getOwnerRecord()->id; // Relation içindeysen LeaveType otomatik gelir

                                if (! $employeeId) {
                                    return [date('Y') => date('Y')];
                                }

                                // Mevcut en büyük yıl kaydını bul
                                $lastYear = \App\Models\EmployeeLeaveBalance::where('employee_id', $employeeId)
                                    ->where('leave_type_id', $leaveTypeId)
                                    ->whereNull('deleted_at')
                                    ->max('year');

                                // Hiç kayıt yoksa bu yıl, varsa son yıl + 1
                                $nextYear = $lastYear ? $lastYear + 1 : date('Y');

                                // Sadece seçilecek yılı options'a koyuyoruz
                                return [$nextYear => $nextYear];
                            })
                            ->default(function ($get) {
                                $employeeId = $get('employee_id');
                                $leaveTypeId = $this->getOwnerRecord()->id;

                                if (! $employeeId) {
                                    return date('Y');
                                }

                                $lastYear = \App\Models\EmployeeLeaveBalance::where('employee_id', $employeeId)
                                    ->where('leave_type_id', $leaveTypeId)
                                    ->whereNull('deleted_at')
                                    ->max('year');

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
                                $employeeId = $get('employee_id');
                                $leaveTypeId = $this->getOwnerRecord()->id;

                                if (! $employeeId || ! $leaveTypeId) {
                                    return 'Aucun congé disponible';
                                }

                                // Récupérer le solde de congé pour cet employé et ce type de congé
                                $leaveBalance = EmployeeLeaveBalance::where('employee_id', $employeeId)
                                    ->where('leave_type_id', $leaveTypeId)
                                    ->whereNull('deleted_at')
                                    ->latest('year')
                                    ->first();

                                // Si aucune donnée n'existe pour cet employé et ce type de congé
                                if (! $leaveBalance) {
                                    return 'Aucun congé disponible';
                                }

                                return $leaveBalance->total_days - $leaveBalance->used_days;
                            }),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            // ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Personnel'),
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
                    ->modalHeading('Ajouter un Congé Acquis')
                    ->mutateFormDataUsing(function (array $data) {
                        $data['created_by'] = auth()->id();

                        // last remaining total days
                        //                        $lastRemainingTotalDays = EmployeeLeaveBalance::where('employee_id', $data['employee_id'])
                        //                            ->where('leave_type_id', $this->getOwnerRecord()->id)
                        //                            ->whereNull('deleted_at')
                        //                            ->latest('year')
                        //                            ->first('total_days');

                        // $data['total_days'] = $lastRemainingTotalDays + $data['total_days'];

                        $employeeBalance = EmployeeLeaveBalance::where('employee_id', $data['employee_id'])
                            ->where('leave_type_id', $this->getOwnerRecord()->id)
                            ->whereNull('deleted_at')
                            ->latest('year')
                            ->first();

                        $totalRemainingDays = $employeeBalance?->total_days - $employeeBalance?->used_days;

                        $data['total_days'] = $data['total_days'] + ($totalRemainingDays);

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->hidden(fn ($record) => $record->leaves()->exists() || EmployeeLeaveBalance::where('employee_id', $record->employee_id)
                            ->where('leave_type_id', $record->leave_type_id)
                            ->where('year', '>', $record->year)
                            ->exists())
                        ->modalHeading('Modifier un Congé Acquis')
                        ->mutateFormDataUsing(function (array $data) {
                            $data['updated_by'] = auth()->id();

                            return $data;
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->hidden(fn ($record) => $record->leaves()->exists() || EmployeeLeaveBalance::where('employee_id', $record->employee_id)
                            ->where('leave_type_id', $record->leave_type_id)
                            ->where('year', '>', $record->year)
                            ->exists() || $record->trashed())
                        ->modalHeading('Supprimer un Congé Acquis'),
                ]),
            ])
            ->bulkActions([
                //
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    protected function canCreate(): bool
    {
        return false;
    }

    protected function canEdit(Model $record): bool
    {
        return false;
    }

    protected function canDelete(Model $record): bool
    {
        return false;
    }
}
