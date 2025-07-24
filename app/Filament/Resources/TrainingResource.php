<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrainingResource\Pages;
use App\Filament\Resources\TrainingResource\RelationManagers;
use App\Models\Training;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class TrainingResource extends Resource
{
    protected static ?string $model = Training::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationGroup = 'Gestion des Personnels';

    //protected static ?int $navigationSort = 10;

    public static function getLabel(): ?string
    {
        return 'Formation';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Formations';
    }

    public static function getNavigationBadge(): ?string
    {
        if (auth()->user()->hasRole('super_admin') || auth()->user()->can('view_all_training')) {
            return static::getModel()::count();
        } else {
            return static::getModel()::where('created_by', auth()->id())->count();
        }
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where(function ($query) {

            if (auth()->user()?->hasRole('super_admin') || auth()->user()?->can('view_all_training')
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
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Fieldset::make('Informations Générales')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nom')
                                    ->placeholder('Nom de la formation')
                                    ->required()
                                    ->validationMessages([
                                        'required' => 'Le nom de la formation est requis.',
                                    ])
                                    ->columnSpanFull(),
                                Forms\Components\DatePicker::make('start_date')
                                    ->label('Date de début')
                                    ->default(now())
                                    ->live()
                                    ->afterStateUpdated(fn (callable $set) => $set('end_date', null))
                                    ->validationMessages([
                                        'required' => 'La date de début est requise.',
                                    ])
                                    ->required(),
                                Forms\Components\DatePicker::make('end_date')
                                    ->label('Date de fin')
                                    ->minDate(fn ($get) => $get('start_date') ? Carbon::parse($get('start_date'))->addDay(1) : null)
                                    ->validationMessages([
                                        'required' => 'La date de fin est requise.',
                                        'min_date' => 'La date de fin doit être supérieure à la date de début.',
                                    ])
                                    ->required(),
                                Forms\Components\Textarea::make('description')
                                    ->label('Description')
                                    ->placeholder('Veuillez décrire la formation en quelques mots ... (facultatif)')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Forms\Components\Toggle::make('status')
                                    ->hiddenOn(['create'])
                                    ->disabled(fn ($record) => ! auth()->user()->hasRole('super_admin') && ($record->employeeTrainings()->exists() || ! auth()->user()->can('update_training')))
                                    ->label('Statut')
                                    ->default(false)
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->helperText('Verte: Actif, Rouge: Inactif'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom de la formation')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Date de début')
                    ->date('d/m/Y')
                    ->badge()
                    ->colors([
                        'success' => fn ($record) => $record->status == true,
                        'danger' => fn ($record) => $record->status == false,
                    ])
                    ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Date de fin')
                    ->date('d/m/Y')
                    ->searchable()
                    ->badge()
                    ->colors([
                        'success' => fn ($record) => $record->status == true,
                        'danger' => fn ($record) => $record->status == false,
                    ]),
                Tables\Columns\ToggleColumn::make('status')
                    ->label('Statut')
                    ->onColor('success')
                    ->offColor('danger')
                    ->disabled(fn ($record) => ! auth()->user()->hasRole('super_admin') && ($record->employeeTrainings()->exists() || ! auth()->user()->can('update_training')))
                    ->sortable(),
                //                Tables\Columns\TextColumn::make('employeeTrainings_count')
                //                    ->label('Participants')
                //                    ->counts('employeeTrainings')
                //                    ->sortable()
                //                    ->badge()
                //                    ->color('info')
                //                    ->toggleable(isToggledHiddenByDefault: false),
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
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->hidden(fn ($record) => $record->employeeTrainings()->exists()),
                ]),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            'employeeTrainings' => RelationManagers\EmployeeTrainingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrainings::route('/'),
            'create' => Pages\CreateTraining::route('/create'),
            'edit' => Pages\EditTraining::route('/{record}/edit'),
            'view' => Pages\ViewTraining::route('/{record}'),
        ];
    }
}
