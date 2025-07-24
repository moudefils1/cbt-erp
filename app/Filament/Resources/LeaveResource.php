<?php

namespace App\Filament\Resources;

use App\Enums\LeaveEnum;
use App\Enums\StateEnum;
use App\Filament\Resources\LeaveResource\Pages;
use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\Leave;
use App\Models\LeaveType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeaveResource extends Resource
{
    protected static ?string $model = Leave::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Gestion de Congés';

    protected static ?int $navigationSort = 3;

    protected static bool $shouldRegisterNavigation = false;

    public static function getLabel(): ?string
    {
        return 'Congés';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Congés';
    }

    public static function getNavigationBadge(): ?string
    {
        if (auth()->user()->hasRole('super_admin') || auth()->user()->can('view_all_leave')) {
            return static::getModel()::count();
        } else {
            return static::getModel()::where('created_by', auth()->id())->count();
        }
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where(function ($query) {

            if (auth()->user()?->hasRole('super_admin') || auth()->user()?->can('view_all_leave')
            ) {
                return $query;
            }

            return $query->where('created_by', auth()->id());
        });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('Congé')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Personnel')
                            ->placeholder('Sélectionnez un personnel')
                            ->options(function (string $operation) {
                                $query = Employee::query();

                                // Only filter by status when creating
                                if ($operation === 'create') {
                                    $query->where('status', true);
                                }

                                return $query->get()->pluck('full_name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (callable $set, $get) => $set('leave_type_id', null))
                            ->validationMessages([
                                'required' => 'Veuillez sélectionner un personnel.',
                            ])
                            ->required(),
                        Forms\Components\Select::make('leave_type_id')
                            ->label('Type de Congé')
                            ->placeholder('Sélectionnez un type de congé')
                            ->options(function ($get) {
                                $employeeId = $get('employee_id');

                                if (! $employeeId) {
                                    return [];
                                }

                                $employee = Employee::find($employeeId);

                                if (! $employee) {
                                    return [];
                                }

                                $leaveTypeIds = $employee->employeeLeaveBalances()
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
//                            ->when(auth()->user()->hasRole("super_admin") || auth()->user()->can('create_custom_leave_type', LeaveType::class), fn($select) => $select->createOptionForm(function ($form) {
//                                $form
//                                    ->schema([
//                                        Forms\Components\Section::make()
//                                            ->schema([
//                                                Forms\Components\Fieldset::make('Type de Congé')
//                                                    ->schema([
//                                                        Forms\Components\TextInput::make('name')
//                                                            ->label('Nom')
//                                                            ->placeholder('Nom du type de congé')
//                                                            ->validationMessages([
//                                                                'required' => 'Champ obligatoire',
//                                                            ])
//                                                            ->columnSpanFull()
//                                                            ->required(),
//                                                        Forms\Components\Textarea::make('description')
//                                                            ->label('Description')
//                                                            ->placeholder('Description du type de congé')
//                                                            ->columnSpanFull(),
//                                                        Forms\Components\Toggle::make('is_paid')
//                                                            ->label('Payé')
//                                                            ->default(false)
//                                                            ->onColor("success")
//                                                            ->offColor("danger")
//                                                            ->helperText("Est-ce que ce congé est payé ?"),
//                                                    ]),
//                                            ]),
//                                    ]);
//
//                                return $form->model(\App\Models\LeaveType::class);
//                            })->createOptionUsing(function ($data) {
//                                $leaveType = \App\Models\LeaveType::create([
//                                    'name' => $data['name'],
//                                    'description' => $data['description'],
//                                    'is_paid' => $data['is_paid'],
//                                    'created_by' => auth()->id(),
//                                ]);
//                                return $leaveType->id;
//                            })
//                            )
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('start_date', null))
                            ->required(),
                    ]),
                Forms\Components\Fieldset::make('Durée')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Date de Début')
                            ->default(now())
                            ->minDate(function ($get, $operation, $record) {

                                $employeeId = $get('employee_id');
                                if (! $employeeId) {
                                    return null;
                                }

                                $employee = Employee::find($employeeId);
                                if (! $employee) {
                                    return null;
                                }

                                $leaveTypeId = $get('leave_type_id');

                                if ($leaveTypeId) {
                                    //                                    $latestLeave = Leave::where('employee_id', $employeeId)
                                    //                                        ->latest('end_date')
                                    //                                        ->first();
                                    //
                                    //                                    if ($latestLeave && $latestLeave->end_date) {
                                    //                                        return \Carbon\Carbon::parse($latestLeave->end_date)->addDay()->format('Y-m-d');
                                    //                                    }
                                    $employeeLeaveBalance = EmployeeLeaveBalance::where('employee_id', $employeeId)
                                        ->where('leave_type_id', $leaveTypeId)  // Bu satır eklendi
                                        ->first();

                                    // Eğer izin bakiyesi bulunursa, o yıl kullanılır
                                    if ($employeeLeaveBalance) {
                                        return \Carbon\Carbon::createFromFormat('Y', $employeeLeaveBalance->year)->startOfYear()->format('Y-m-d');
                                    }
                                }

                                return $employee->hiring_date?->format('Y-m-d');
                            })
                            ->validationMessages([
                                'required' => 'La date de début est obligatoire.',
                                'min_date' => 'La date de début ne doit pas être antérieure à la date d\'embauche du personnel.',
                            ])
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('end_date', null))
                            ->required(),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Date de Fin')
                            ->after('start_date')
                            ->minDate(fn ($get) => $get('start_date') ? \Carbon\Carbon::parse($get('start_date'))->addDay() : null)
                            ->maxDate(fn ($get) => $get('employee_id') ? Employee::find($get('employee_id'))->end_date : null)
                            ->validationMessages([
                                'required' => 'La date de fin est obligatoire.',
                                'min_date' => 'La date de fin ne doit pas être antérieure à la date de début.',
                                'after' => 'La date de fin ne doit pas être antérieure à la date de début.',
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

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('leaveType.name')
                    ->label('Nom du Type de Congé')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Personnel')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('employee', function (Builder $query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('surname', 'like', "%{$search}%");
                        });
                    }),
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
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->modalHeading('Détails du Congé'),
                    Tables\Actions\EditAction::make()
                        ->hidden(fn ($record) => $record->status->is(LeaveEnum::APPROVED))
                        ->modalHeading('Modifier un Congé')
                        ->mutateFormDataUsing(function (array $data, $record) {
                            $data['updated_by'] = auth()->id();

                            $start = \Carbon\Carbon::parse($data['start_date']);
                            $end = \Carbon\Carbon::parse($data['end_date']);
                            $used_days = 0;

                            for ($date = $start; $date->lte($end); $date->addDay()) {
                                // Skip weekends (0 = Sunday, 6 = Saturday)
                                if (! in_array($date->dayOfWeek, [0, 6])) {
                                    $used_days++;
                                }
                            }

                            $data['used_days'] = $used_days;

                            // if end date is passed, then the leave is approved
                            if (\Carbon\Carbon::parse($data['end_date'])->isPast()) {
                                $data['status'] = LeaveEnum::APPROVED;
                                $data['approved_by'] = auth()->id();
                                $data['approved_at'] = now();
                                $data['state'] = StateEnum::COMPLETED;
                            } else {
                                $data['status'] = LeaveEnum::PENDING;
                                $data['state'] = StateEnum::STANDBY;
                            }

                            // calculate the number of days between the start and end date
                            // $data['used_days'] = \Carbon\Carbon::parse($data['start_date'])->diffInDays(\Carbon\Carbon::parse($data['end_date'])) + 1;

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

                                // Update the employee status to ON_LEAVE
                                $record->employee()->update(['status' => \App\Enums\EmployeeStatusEnum::ON_LEAVE]);
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaves::route('/'),
            // 'create' => Pages\CreateLeave::route('/create'),
            // 'edit' => Pages\EditLeave::route('/{record}/edit'),
        ];
    }
}
