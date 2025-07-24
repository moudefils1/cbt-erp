<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use App\Enums\EmployeeTypeEnum;
use App\Enums\StatusEnum;
use App\Models\Location;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EmployeePositionsRelationManager extends RelationManager
{
    protected static string $relationship = 'employeePositions';

    public static function getLabel(): ?string
    {
        return 'Poste Occupé';
    }

    public static function getPluralModelLabel(): ?string
    {
        return 'Postes Occupés';
    }

    protected static ?string $icon = 'heroicon-o-rectangle-stack';

    protected static ?string $title = 'Postes Occupés';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Fieldset::make('Dates de Début et de Fin du Fonction/Contrat')
                            ->schema([
                                Forms\Components\Placeholder::make('hiring_date')
                                    ->label('Date d\'Embauche')
                                    ->content($this->ownerRecord->hiring_date->format('d/m/Y')),
                                Forms\Components\Placeholder::make('end_date')
                                    ->visible($this->ownerRecord->employee_type_id == EmployeeTypeEnum::CDD)
                                    ->label('Date de Fin du Fonction/Contrat')
                                    ->content($this->ownerRecord->end_date?->format('d/m/Y')),
                            ])->columnSpanFull(),
                        Forms\Components\Fieldset::make()
                            ->schema([
                                Forms\Components\Select::make('location_id')
                                    ->label('Département')
                                    ->options(Location::get()->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(fn (callable $set) => $set('task_id', null))
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->validationMessages([
                                        'required' => 'Veiullez choisir un Département',
                                    ])
                                    ->required(),
                                Forms\Components\Select::make('task_id')
                                    ->label('Fontion')
                                    ->options(function (callable $get) {
                                        $locationId = $get('location_id');

                                        if (! $locationId) {
                                            return [];
                                        }

                                        return Task::where('location_id', $locationId)
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->validationMessages([
                                        'required' => 'Veuillez choisir une Fonction',
                                    ])
                                    ->required(),
                                Forms\Components\DatePicker::make('position_start_date')
                                    ->label('Date de début')
                                    ->minDate($this->ownerRecord->hiring_date->format('Y-m-d'))
                                    ->live()
                                    ->afterStateUpdated(fn (callable $set) => $set('position_end_date', null))
                                    ->rules([
                                        'required',
                                        'after_or_equal:'.$this->ownerRecord->hiring_date->format('Y-m-d'),
                                    ])
                                    ->validationMessages([
                                        'after_or_equal' => 'Veillez choisir une date postérieure ou égale à la date du: '.$this->ownerRecord->hiring_date->format('d/m/Y'),
                                        'before' => 'La Date de début doit être antérieure à la Date de fin',
                                        'required' => 'Date obligatoire',
                                    ])
                                    ->required(),
                                Forms\Components\Datepicker::make('position_end_date')
                                    ->visible($this->ownerRecord->employee_type_id == EmployeeTypeEnum::CDD)
                                    ->label('Date de fin')
                                    ->minDate(fn ($get) => $get('position_start_date'))
                                    ->maxDate($this->ownerRecord->end_date?->format('Y-m-d'))
                                    ->after('position_start_date')
                                    ->rules([
                                        'required',
                                        'before_or_equal:'.$this->ownerRecord->end_date?->format('Y-m-d'),
                                    ])
                                    ->validationMessages([
                                        'before_or_equal' => 'Veillez choisir une date antérieure ou égale à la date du: '.$this->ownerRecord->end_date?->format('d/m/Y'),
                                        'after' => 'La Date de fin doit être postérieure à la Date de début',
                                        'required' => 'Date obligatoire',
                                    ])
                                    ->required(),
                                Forms\Components\Textarea::make('description')
                                    ->label('Description')
                                    ->placeholder('Description du poste occupé (optionnel)')
                                    ->columnSpanFull(),
                                Forms\Components\SpatieMediaLibraryFileUpload::make('employee_position_documents')
                                    ->label('Documents relatifs au poste occupé (optionnel)')
                                    ->collection('employee_position_documents')
                                    ->acceptedFileTypes([
                                        'application/pdf',
                                        'text/csv',
                                        'text/xls',
                                        'application/vnd.ms-excel',
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    ])
                                    ->downloadable()
                                    ->openable()
                                    ->multiple()
                                    ->reorderable()
                                    ->maxFiles(150)
                                    ->helperText('Les fichiers doivent être au format PDF ou Excel. Vous pouvez ajouter plusieurs fichiers à la fois.')
                                    ->validationMessages([
                                        'required' => 'Le document est requis',
                                        'max_files' => 'Vous ne pouvez pas ajouter plus de 150 fichiers',
                                    ])
                                    ->columnSpan('full'),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            // ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Département'),
                Tables\Columns\TextColumn::make('task.name')
                    ->label('Fonction'),
                Tables\Columns\TextColumn::make('position_start_date')
                    ->label('Date de début')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('position_end_date')
                    ->label('Date de fin')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Créé par')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->date('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updatedBy.name')
                    ->label('Modifié par')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->getStateUsing(fn ($record) => $record->updated_by ? $record->updated_at : null)
                    ->label('Modifié le')
                    ->date('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading('Ajouter un Poste Occupé')
                    ->mutateFormDataUsing(function ($data) {
                        $data['created_by'] = auth()->id();

                        if (isset($data['position_end_date']) && $data['position_end_date'] < now()) {
                            $data['status'] = StatusEnum::INACTIVE;
                        } else {
                            $data['status'] = StatusEnum::ACTIVE;
                        }

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->modalHeading('Modifier un Poste Occupé')
                        ->mutateFormDataUsing(function ($data) {
                            $data['updated_by'] = auth()->id();

                            if (isset($data['position_end_date']) && $data['position_end_date'] < now()) {
                                $data['status'] = StatusEnum::INACTIVE;
                            } else {
                                $data['status'] = StatusEnum::ACTIVE;
                            }

                            return $data;
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->modalHeading('Supprimer un Poste Occupé'),
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
