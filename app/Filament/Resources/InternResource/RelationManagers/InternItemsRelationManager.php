<?php

namespace App\Filament\Resources\InternResource\RelationManagers;

use App\Enums\EmployeeStatusEnum;
use App\Enums\StateEnum;
use App\Models\Employee;
use App\Models\Location;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InternItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'internItems';

    public static function getLabel(): ?string
    {
        return 'Départements de Stage';
    }

    protected static function getPluralModelLabel(): ?string
    {
        return 'Départements de Stage';
    }

    protected static ?string $title = 'Départements de Stage';

    protected static ?string $label = 'Départements de Stage';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Fieldset::make('Dates de Début et de Fin du Stage')
                            ->schema([
                                Forms\Components\Placeholder::make('internship_start_date')
                                    ->label('Début de Stage')
                                    ->content($this->ownerRecord->internship_start_date->format('d/m/Y')),
                                Forms\Components\Placeholder::make('internship_end_date')
                                    ->label('Fin de Stage')
                                    ->content($this->ownerRecord->internship_end_date->format('d/m/Y')),
                            ])->columnSpanFull(),
                        Forms\Components\Select::make('location_id')
                            ->label('Département')
                            ->options(fn () => Location::whereHas('employees') // Sadece çalışanı olan locationları getir
                                ->pluck('name', 'id')
                                ->toArray()
                            )
                            ->placeholder('Choisissez')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('employee_id', null))
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->validationMessages([
                                'required' => 'Veiullez choisir un Département',
                            ])
                            ->required(),
                        Forms\Components\Select::make('employee_id')
                            ->label('Encadreur')
                            ->relationship('employee', 'name')
                            ->placeholder('Choisissez un Encadreur')
                            ->options(function (callable $get) {
                                $locationId = $get('location_id');

                                if (! $locationId) {
                                    return [];
                                }

                                return Employee::where('location_id', $locationId)
                                    ->where('status', EmployeeStatusEnum::WORKING)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->reactive()
                            ->validationMessages([
                                'required' => 'Veuillez choisir un Encadreur',
                            ])
                            ->required(),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Date de Début du Stage dans ce Département')
                            ->default(now())
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('end_date', null))
                            ->minDate($this->ownerRecord->internship_start_date?->format('Y-m-d'))
                            ->rules([
                                'required',
                                'after_or_equal:'.$this->ownerRecord->internship_start_date->format('Y-m-d'),
                            ])
                            ->validationMessages([
                                'after_or_equal' => 'Veillez choisir une date postérieure ou égale à la date du: '.$this->ownerRecord->internship_start_date->format('d/m/Y'),
                                'required' => 'Date obligatoire',
                            ]),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Date de Fin du Stage dans ce Département')
                            ->minDate(fn (callable $get) => $get('start_date') ? \Carbon\Carbon::parse($get('start_date'))->addDay()->format('Y-m-d') : null)
                            ->maxDate($this->ownerRecord->internship_end_date?->format('Y-m-d'))
                            ->required()
                            ->rules([
                                'required',
                                'before_or_equal:'.$this->ownerRecord->internship_end_date->format('Y-m-d'),
                            ])
                            ->after('start_date')
                            ->validationMessages([
                                'before_or_equal' => 'Veillez choisir une date antérieure ou égale à la date du: '.$this->ownerRecord->internship_end_date->format('d/m/Y'),
                                'after' => 'Veillez choisir une date postérieure à la date du: '.$this->ownerRecord->internship_start_date->format('d/m/Y'),
                                'required' => 'Date obligatoire',
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Description du Stage')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Fieldset::make('Statut du Stagiaire dans ce Département')
                    ->visibleOn(['view'])
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->hiddenLabel()
                            ->options(StateEnum::class)
                            ->required()
                            ->validationMessages([
                                'required' => 'Le statut du stage est obligatoire',
                            ])
                            ->columnSpan('full'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            // ->recordTitleAttribute('intern_id')
            ->columns([
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Département'),
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Encadreur'),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Date de début')
                    ->badge()
                    ->color(fn ($record) => match ($record->status) {
                        StateEnum::COMPLETED => 'success',
                        StateEnum::IN_PROGRESS => 'info',
                        StateEnum::STANDBY => 'warning',
                        default => 'gray',
                    })
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Date de fin')
                    ->badge()
                    ->color(fn ($record) => match ($record->status) {
                        StateEnum::COMPLETED => 'success',
                        default => 'gray',
                    })
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->visible(fn () => auth()->user()->hasRole('super_admin'))
                    ->label('Créé par')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->date('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->mutateFormDataUsing(function ($data) {
                        $data['created_by'] = auth()->id();

                        if ($data['start_date'] > now()->format('Y-m-d')) {
                            $data['status'] = StateEnum::STANDBY;
                        } elseif ($data['start_date'] < now()->format('Y-m-d') && $data['end_date'] > now()->format('Y-m-d')) {
                            $data['status'] = StateEnum::IN_PROGRESS;
                        } elseif ($data['start_date'] < now()->format('Y-m-d') && $data['end_date'] == now()->format('Y-m-d')) {
                            $data['status'] = StateEnum::IN_PROGRESS;
                        } elseif ($data['start_date'] == now()->format('Y-m-d') && $data['end_date'] > now()->format('Y-m-d')) {
                            $data['status'] = StateEnum::IN_PROGRESS;
                        } else {
                            $data['status'] = StateEnum::COMPLETED;
                        }

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->mutateFormDataUsing(function ($data, $record) {

                            $data['updated_by'] = auth()->id();

                            if ($data['start_date'] > now()->format('Y-m-d')) {
                                $data['status'] = StateEnum::STANDBY;
                            } elseif ($data['start_date'] < now()->format('Y-m-d') && $data['end_date'] > now()->format('Y-m-d')) {
                                $data['status'] = StateEnum::IN_PROGRESS;
                            } elseif ($data['start_date'] < now()->format('Y-m-d') && $data['end_date'] == now()->format('Y-m-d')) {
                                $data['status'] = StateEnum::IN_PROGRESS;
                            } elseif ($data['start_date'] == now()->format('Y-m-d') && $data['end_date'] > now()->format('Y-m-d')) {
                                $data['status'] = StateEnum::IN_PROGRESS;
                            } else {
                                $data['status'] = StateEnum::COMPLETED;
                            }

                            return $data;
                        }),
                    Tables\Actions\DeleteAction::make(),
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
