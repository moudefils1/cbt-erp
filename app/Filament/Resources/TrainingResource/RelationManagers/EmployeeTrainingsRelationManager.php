<?php

namespace App\Filament\Resources\TrainingResource\RelationManagers;

use App\Enums\EmployeeStatusEnum;
use App\Filament\Resources\EmployeeResource;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmployeeTrainingsRelationManager extends RelationManager
{
    protected static string $relationship = 'employeeTrainings';

    protected static ?string $title = 'Bénéficiaries de la Formation';

    protected static ?string $label = 'Bénéficiaire de la Formation';

    protected static ?string $pluralLabel = 'Bénéficiaires de la Formation';

    protected static ?string $icon = 'heroicon-0-trophy';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make()
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Personnel')
                            ->placeholder('Sélectionnez')
                            ->options(function (string $operation) {
                                $query = Employee::query()
                                    ->whereIn('status', [
                                        EmployeeStatusEnum::WORKING,
                                    ]);

                                // Only show employees who are working if the operation is create
                                if ($operation === 'create') {
                                    $query->where('status', EmployeeStatusEnum::WORKING);
                                }

                                return $query->get()->pluck('full_name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->validationMessages([
                                'required' => 'Champ obligatoire',
                            ])
                            ->required()
                            ->columnSpanFull(),
                        //                        Forms\Components\DatePicker::make('start_date')
                        //                            ->label('Date de début de formation')
                        //                            ->placeholder('Sélectionnez')
                        //                            ->default(now())
                        //                            ->minDate(fn () => $this->ownerRecord->start_date)
                        //                            ->validationMessages([
                        //                                'required' => 'Champ obligatoire',
                        //                                'min' => 'La date de début de formation doit être supérieure ou égale à la date de début de la formation.',
                        //                            ])
                        //                            ->live()
                        //                            ->afterStateUpdated(fn (callable $set) => $set('end_date', null))
                        //                            ->required(),
                        //                        Forms\Components\DatePicker::make('end_date')
                        //                            ->label('Date de fin de formation')
                        //                            ->placeholder('Sélectionnez')
                        //                            ->minDate(fn (callable $get) => $get('start_date'))
                        //                            ->validationMessages([
                        //                                'required' => 'Champ obligatoire',
                        //                                'min' => 'La date de fin de formation doit être supérieure ou égale à la date de début de la formation.',
                        //                            ])
                        //                            ->required(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Nom du Personnel')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('employee', function (Builder $query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('surname', 'like', "%{$search}%");
                        });
                    }),
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
                    ->visible(fn () => auth()->user()->hasRole('super_admin') || $this->ownerRecord->status == true)
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['created_by'] = auth()->id();

                        // set employee to in training
                        $employee = Employee::find($data['employee_id']);
                        if ($employee) {
                            $employee->on_training = true;
                            $employee->save();
                        }

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    // ->url(fn ($record) => EmployeeResource::getUrl('view', ['record' => $record->employee_id])),
                    Tables\Actions\EditAction::make()
                        ->mutateFormDataUsing(function (array $data): array {
                            $data['updated_by'] = auth()->id();

                            return $data;
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->action(function ($record) {
                            // set employee to not in training
                            $employee = Employee::find($record->employee_id);
                            if ($employee) {
                                $employee->on_training = false;
                                $employee->save();
                            }
                            $record->delete();
                        }),
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
}
