<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use App\Enums\EmployeeStatusEnum;
use App\Enums\LeaveEnum;
use App\Enums\StateEnum;
use App\Models\EmployeeLeaveBalance;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Settings\AppSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Components\Tab;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class LeavesRelationManager extends RelationManager
{
    protected static string $relationship = 'leaves';

    protected static ?string $title = 'Congés';

    protected static ?string $icon = 'heroicon-o-calendar-days';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('Congé')
                    ->schema([
                        Forms\Components\Select::make('leave_type_id')
                            ->label('Type de Congé')
                            ->placeholder('Sélectionnez un type de congé')
                            ->options(function () {
                                $leaveTypeIds = $this->ownerRecord->employeeLeaveBalances()
                                    ->pluck('leave_type_id')
                                    ->unique()
                                    ->toArray();

                                return LeaveType::whereIn('id', $leaveTypeIds)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->validationMessages([
                                'required' => 'Veillez sélectionner un type de congé.',
                            ])
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('start_date', null))
                            ->required()
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Fieldset::make('Durée')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Date de Début')
                            ->default(now())
                            ->minDate(function ($get, $operation, $record) {
                                $leaveTypeId = $get('leave_type_id');
                                if ($leaveTypeId) {
                                    $latestLeave = Leave::where('employee_id', $this->ownerRecord->id)
                                        // ->where('leave_type_id', $leaveTypeId)
                                        ->latest('end_date')
                                        ->first();

                                    if ($latestLeave && $latestLeave->end_date) {
                                        return Carbon::parse($latestLeave->end_date)->addDay()->format('Y-m-d');
                                    }
                                }

                                // Fallback: Use the employee's leave balance year and set the date to the first day of that year
                                if ($this->ownerRecord->employeeLeaveBalance) {
                                    return Carbon::createFromFormat('Y', $this->ownerRecord->employeeLeaveBalance->year)->startOfYear()->format('Y-m-d');
                                }

                                // Default to the current year if no employee leave balance exists
                                return now()->startOfYear()->format('Y-m-d');
                            })
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('end_date', null))
                            ->validationMessages([
                                'required' => 'La date de début est obligatoire.',
                            ])
                            ->required(),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Date de Fin')
                            ->after('start_date')
                            ->minDate(fn ($get) => $get('start_date') ? Carbon::parse($get('start_date'))->addDay() : null)
                            ->maxDate($this->ownerRecord->end_date)?->format('Y-m-d')
                            ->validationMessages([
                                'required' => 'La date de fin est obligatoire.',
                                'after' => 'La date de fin doit être après la date de début.',
                                'min_date' => 'La date de fin doit être après la date de début.',
                                'max_date' => 'La date de fin ne doit pas dépasser le nombre de jours de congé restants.',
                            ])
                            ->required(),
                    ]),
                Forms\Components\Fieldset::make('Raision du Rejet')
                    ->visibleOn('view')
                    ->visible(fn ($record) => $record && $record->status?->is(LeaveEnum::REJECTED))
                    ->schema([
                        Forms\Components\Placeholder::make('rejected_reason')
                            ->hiddenLabel()
                            ->content(fn ($record) => $record->rejected_reason)
                            ->columnSpanFull()
                            ->disabled(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            // ->recordTitleAttribute('id')
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('leaveType.name')
                    ->label('Nom du Type de Congé')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Date de Début')
                    ->date('d/m/Y')
                    ->badge()
                    ->colors([
                        'success' => fn ($record) => $record->status->is(LeaveEnum::APPROVED),
                        'danger' => fn ($record) => $record->status->is(LeaveEnum::REJECTED),
                        'warning' => fn ($record) => $record->status->is(LeaveEnum::PENDING),
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Date de Fin')
                    ->date('d/m/Y')
                    ->badge()
                    ->colors([
                        'success' => fn ($record) => $record->status->is(LeaveEnum::APPROVED),
                        'danger' => fn ($record) => $record->status->is(LeaveEnum::REJECTED),
                        'warning' => fn ($record) => $record->status->is(LeaveEnum::PENDING),
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('used_days')
                    ->label('Durées (Jrs)')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('state')
                    ->label('Etat Actuel')
                    ->badge()
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
                Tables\Filters\SelectFilter::make('leave_type_id')
                    ->label('Type de Congé')
                    ->options(LeaveType::all()->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options(LeaveEnum::class)
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->visible(fn () => auth()->user()->hasRole('super_admin') || $this->ownerRecord->status == EmployeeStatusEnum::WORKING || $this->ownerRecord->status == EmployeeStatusEnum::ON_LEAVE)
                    ->modalHeading('Ajouter un Congé')
                    ->mutateFormDataUsing(function (array $data, $record) {
                        $data['created_by'] = auth()->id();

                        $settings = app(AppSettings::class);

                        $start = Carbon::parse($data['start_date']);
                        $end = Carbon::parse($data['end_date']);

                        // Count only working days (excluding weekends)
                        $used_days = 0;
                        for ($date = $start->copy(); $date->lt($end); $date->addDay()) {
                            // Check if current day is a working day (not a weekend)
                            if (\App\Helpers\AppHelper::isWorkingDay($date, $settings->working_days_per_week)) {
                                $used_days++;
                            }
                        }

                        $data['used_days'] = $used_days;

                        $data['employee_leave_balance_id'] = EmployeeLeaveBalance::where('leave_type_id', $data['leave_type_id'])
                            ->where('employee_id', $this->ownerRecord->id)
                            ->latest()
                            ->first()->id;

                        // if end date is passed, then the leave is approved
                        if (Carbon::parse($data['end_date'])->isPast()) {
                            $data['status'] = LeaveEnum::APPROVED;
                            $data['approved_by'] = auth()->id();
                            $data['approved_at'] = now();
                            $data['state'] = StateEnum::COMPLETED;

                            // update leave balance
                            $employee_leave_balance = EmployeeLeaveBalance::where('leave_type_id', $data['leave_type_id'])
                                ->where('employee_id', $this->ownerRecord->id)
                                ->latest()
                                ->first();

                            $employee_leave_balance->update([
                                'used_days' => $employee_leave_balance->used_days + $used_days,
                            ]);
                        } else {
                            $data['status'] = LeaveEnum::PENDING;
                        }

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->modalHeading('Détails du Congé'),
                    Tables\Actions\EditAction::make()
                        ->hidden(fn ($record) => $record->status->is(LeaveEnum::APPROVED))
                        ->modalHeading('Modifier un Congé')
                        ->mutateFormDataUsing(function (array $data, $record) {
                            $data['updated_by'] = auth()->id();

                            $start = Carbon::parse($data['start_date']);
                            $end = Carbon::parse($data['end_date']);
                            $used_days = 0;

                            for ($date = $start; $date->lte($end); $date->addDay()) {
                                // Skip weekends (0 = Sunday, 6 = Saturday)
                                if (! in_array($date->dayOfWeek, [0, 6])) {
                                    $used_days++;
                                }
                            }

                            $data['used_days'] = $used_days;

                            // if end date is passed, then the leave is approved
                            if (Carbon::parse($data['end_date'])->isPast()) {
                                $data['status'] = LeaveEnum::APPROVED;
                                $data['approved_by'] = auth()->id();
                                $data['approved_at'] = now();
                                $data['state'] = StateEnum::COMPLETED;
                            } else {
                                $data['status'] = LeaveEnum::PENDING;
                                $data['state'] = StateEnum::STANDBY;
                            }

                            return $data;
                        }),
                    Tables\Actions\Action::make('approveLeave')
                        ->visible(fn ($record) => $record->status->is(LeaveEnum::PENDING) && auth()->user()?->can('approve_leave'))
                        ->label('Approuver')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Approuver un Congé')
                        ->modalDescription('Êtes-vous sûr de vouloir approuver ce congé ?')
                        ->modalIcon('heroicon-o-check-circle')
                        ->action(function ($record) {

                            $used_days = $record->used_days;

                            // update used days for the employee leave balance by adding the used days
                            $employee_leave_balance = EmployeeLeaveBalance::where('leave_type_id', $record->leave_type_id)
                                ->where('employee_id', $record->employee_id)
                                ->latest()
                                ->first();

                            $employee_leave_balance->update([
                                'used_days' => $employee_leave_balance->used_days + $used_days,
                            ]);

                            $record->update([
                                'status' => LeaveEnum::APPROVED,
                                'approved_by' => auth()->id(),
                                'approved_at' => now(),

                                // Clear the rejected fields
                                'rejected_by' => null,
                                'rejected_at' => null,
                                'rejected_reason' => null,

                                // Updated by
                                'updated_by' => auth()->id(),
                            ]);

                            // Update the employee on_leave status if the leave is currently active
                            if ($record->start_date <= now() && $record->end_date >= now()) {

                                // Update state to IN_PROGRESS (default state is STANDBY)
                                $record->update(['state' => StateEnum::IN_PROGRESS]);

                                // Update the employee on_leave status
                                $record->employee()->update(['on_leave' => true]);
                            }
                        })
                        ->successNotificationTitle('Le congé a été approuvé.'),
                    Tables\Actions\Action::make('rejectLeave')
                        ->visible(fn ($record) => ($record->status->is(LeaveEnum::PENDING) || $record->status->is(LeaveEnum::APPROVED)) && auth()->user()?->can('reject_leave'))
                        ->hidden(fn ($record) => $record->status->is(LeaveEnum::APPROVED) && $record->state->is(StateEnum::COMPLETED))
                        ->label('Rejeter')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Rejeter un Congé')
                        ->modalDescription('Êtes-vous sûr de vouloir rejeter ce congé ?')
                        ->modalIcon('heroicon-o-x-circle')
                        ->form([
                            Forms\Components\Textarea::make('reason')
                                ->label('Raison du rejet')
                                ->placeholder('Veillez saisir la raison du rejet.')
                                ->maxLength(500)
                                ->validationMessages([
                                    'required' => 'La raison du rejet est obligatoire.',
                                    'max' => 'La raison du rejet ne doit pas dépasser 500 caractères.',
                                ])
                                ->required(),
                        ])
                        ->action(function ($record, array $data) {
                            // If the leave was previously approved, we need to subtract the used days
                            if ($record->status->is(LeaveEnum::APPROVED)) {
                                $used_days = $record->used_days;

                                // Get the employee leave balance
                                $employee_leave_balance = EmployeeLeaveBalance::where('leave_type_id', $record->leave_type_id)
                                    ->where('employee_id', $record->employee_id)
                                    ->latest()
                                    ->first();

                                // Subtract the used days
                                $employee_leave_balance->update([
                                    'used_days' => max(0, $employee_leave_balance->used_days - $used_days),
                                ]);

                                // Reset employee status if leave was in progress
                                if ($record->state->is(StateEnum::IN_PROGRESS)) {
                                    $record->employee()->update(['on_leave' => false]);
                                    $record->employee()->update(['status' => \App\Enums\EmployeeStatusEnum::WORKING]);
                                    $record->update(['state' => StateEnum::COMPLETED]);
                                }
                            }

                            $record->update([
                                'status' => LeaveEnum::REJECTED,
                                'rejected_by' => auth()->id(),
                                'rejected_at' => now(),
                                'rejected_reason' => $data['reason'],

                                // Clear the approved fields
                                'approved_by' => null,
                                'approved_at' => null,

                                // Updated by
                                'updated_by' => auth()->id(),
                            ]);
                        })
                        ->successNotificationTitle('Le congé a été rejeté.'),
                    Tables\Actions\DeleteAction::make()
                        ->visible(fn () => auth()->user()->hasRole('super_admin') || $this->ownerRecord->status == EmployeeStatusEnum::WORKING || $this->ownerRecord->status == EmployeeStatusEnum::ON_LEAVE)
                        ->modalHeading('Supprimer un Congé')
                        ->modalDescription('Êtes-vous sûr de vouloir supprimer ce congé ?')
                        ->modalIcon('heroicon-o-trash')
                        ->action(function ($record) {

                            // Si le congé était approuvé, soustraire les jours utilisés du solde de congé
                            if ($record->status->is(LeaveEnum::APPROVED)) {
                                $used_days = $record->used_days;

                                // Récupérer le solde de congé de l'employé
                                $employee_leave_balance = EmployeeLeaveBalance::where('leave_type_id', $record->leave_type_id)
                                    ->where('employee_id', $record->employee_id)
                                    ->latest()
                                    ->first();

                                // Soustraire les jours utilisés
                                if ($employee_leave_balance) {
                                    $employee_leave_balance->update([
                                        'used_days' => max(0, $employee_leave_balance->used_days - $used_days),
                                    ]);
                                }

                                // Réinitialiser le statut de l'employé si le congé était en cours
                                if ($record->state->is(StateEnum::IN_PROGRESS)) {
                                    $record->employee()->update(['on_leave' => false]);
                                    $record->employee()->update(['status' => \App\Enums\EmployeeStatusEnum::WORKING]);
                                }
                            }
                            // Supprimer l'enregistrement
                            $record->delete();
                        })
                        ->successNotificationTitle('Le congé a été supprimé.'),
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

    public function getTabs(): array
    {
        $tabs = [];

        $tabs['all'] = Tab::make('Total')
            ->badge($this->ownerRecord->leaves()->count());

        $tabs['pending'] = Tab::make('En attente')
            ->badge($this->ownerRecord->leaves()->where('status', LeaveEnum::PENDING)->count())
            ->badgeIcon('heroicon-o-calendar-days')
            ->badgeColor('warning')
            ->modifyQueryUsing(function ($query) {
                return $query->where('status', LeaveEnum::PENDING);
            });

        $tabs['approved'] = Tab::make('Approuvé')
            ->badge($this->ownerRecord->leaves()->where('status', LeaveEnum::APPROVED)->count())
            ->badgeIcon('heroicon-o-calendar-days')
            ->badgeColor('success')
            ->modifyQueryUsing(function ($query) {
                return $query->where('status', LeaveEnum::APPROVED);
            });

        $tabs['rejected'] = Tab::make('Rejeté')
            ->badge($this->ownerRecord->leaves()->where('status', LeaveEnum::REJECTED)->count())
            ->badgeIcon('heroicon-o-calendar-days')
            ->badgeColor('danger')
            ->modifyQueryUsing(function ($query) {
                return $query->where('status', LeaveEnum::REJECTED);
            });

        return $tabs;
    }
}
