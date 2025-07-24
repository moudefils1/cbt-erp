<?php

namespace App\Filament\Resources;

use App\Enums\GenderEnum;
use App\Enums\InternshipTypeEnum;
use App\Enums\StateEnum;
use App\Filament\Resources\InternResource\Pages;
use App\Filament\Resources\InternResource\RelationManagers\InternItemsRelationManager;
use App\Models\Intern;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @method void prepareToAttachMedia(Media $media, FileAdder $fileAdder)
 */
class InternResource extends Resource
{
    use InteractsWithMedia;

    protected static ?string $model = Intern::class;

    public static function getModelLabel(): string
    {
        return 'Stagiaire';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Stagiaires';
    }

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static ?string $navigationGroup = 'Gestion des Stagiaires';

    protected static ?string $navigationLabel = 'Stagiaires';

    protected static ?int $navigationSort = 10;

    public static function getNavigationBadge(): ?string
    {
        if (auth()->user()->hasRole('super_admin') || auth()->user()->can('view_all_intern')) {
            return static::getModel()::count();
        } else {
            return static::getModel()::where('created_by', auth()->id())->count();
        }
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where(function ($query) {

            if (auth()->user()?->hasRole('super_admin') || auth()->user()?->can('view_all_intern')
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
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Fieldset::make('Informations Personnelles')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nom et Prénom')
                                            ->placeholder('Nom et Prénom du Stagiaire')
                                            ->string()
                                            ->maxLength(255)
                                            ->columnSpan('full')
                                            ->required(),
                                        Forms\Components\Select::make('gender')
                                            ->label('Genre')
                                            ->options(GenderEnum::class)
                                            ->placeholder('Choisissez')
                                            ->validationMessages([
                                                'required' => 'Le genre du stagiaire est obligatoire',
                                            ])
                                            ->required(),
                                        Forms\Components\TextInput::make('university')
                                            ->label('Université')
                                            ->placeholder('Nom de l\'Université')
                                            ->required()
                                            ->string()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('department')
                                            ->label('Filière')
                                            ->placeholder('Nom de la Filière')
                                            ->required()
                                            ->string()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('phone')
                                            ->label('Téléphone')
                                            ->placeholder('Numéro de Téléphone')
                                            ->numeric()->unique(
                                                'interns',
                                                'phone',
                                                ignoreRecord: true,
                                                modifyRuleUsing: function ($rule) {
                                                    return $rule->whereNull('deleted_at');
                                                }
                                            )
                                            ->minLength(8)
                                            ->maxLength(13)
                                            ->required(),
                                        Forms\Components\TextInput::make('email')
                                            ->label('E-mail')
                                            ->placeholder('Adresse E-mail')
                                            ->email()
                                            ->unique(
                                                'interns',
                                                'email',
                                                ignoreRecord: true,
                                                modifyRuleUsing: function ($rule) {
                                                    return $rule->whereNull('deleted_at');
                                                }
                                            )
                                            ->required()
                                            ->maxLength(50),
                                        Forms\Components\Fieldset::make('Addresse')
                                            ->schema([
                                                Forms\Components\Textarea::make('address')
                                                    ->hiddenLabel()
                                                    ->placeholder('Adresse du Stagiaire')
                                                    ->placeholder("Ex: Rue de 40 m, Naga 2, N'Djaména")
                                                    ->required()
                                                    ->string()
                                                    ->maxLength(255)
                                                    ->columnSpan('full'),
                                            ]),
                                    ])->columns(3),
                            ]),

                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Fieldset::make('Informations sur le Stage')
                                    ->schema([
                                        Forms\Components\Select::make('grade_id')
                                            ->label('Niveau d\'Etude du Stagiaire')
                                            ->relationship('grade', 'name')
                                            ->placeholder('Choisissez')
                                            ->preload()
                                            ->searchable()
                                            ->reactive()
                                            ->when(auth()->user()->hasRole('super_admin') || auth()->user()->can('create_custom_grade'), fn ($select) => $select->createOptionForm(function ($form) {
                                                $form
                                                    ->schema([
                                                        Forms\Components\TextInput::make('name')
                                                            ->label('Nom du Niveau')
                                                            ->placeholder('Ex: Licence, Master, etc.')
                                                            ->required()
                                                            ->string()
                                                            ->maxLength(255),
                                                        Forms\Components\Fieldset::make('description')
                                                            ->label('Description')
                                                            ->schema([
                                                                Forms\Components\Textarea::make('description')
                                                                    ->hiddenLabel()
                                                                    ->placeholder('Décrivez le niveau en quelques mots')
                                                                    ->string()
                                                                    ->maxLength(255)
                                                                    ->columnSpan('full'),
                                                            ]),
                                                    ]);

                                                return $form->model(\App\Models\Grade::class);
                                            })
                                                ->createOptionUsing(function ($data) {
                                                    $grade = \App\Models\Grade::create([
                                                        'name' => $data['name'],
                                                        'description' => $data['description'],
                                                        'created_by' => auth()->id(),
                                                    ]);

                                                    return $grade->id;
                                                })
                                            )
                                            ->required(),
                                        Forms\Components\Select::make('internship_type')
                                            ->label('Type de Stage')
                                            ->options(InternshipTypeEnum::class)
                                            ->placeholder('Choisissez')
                                            ->required(),
                                        Forms\Components\DatePicker::make('internship_start_date')
                                            ->label('Début de Stage')
                                            ->default(now())
                                            ->live()
                                            ->afterStateUpdated(fn (callable $set) => $set('internship_end_date', null))
                                            ->readOnly(fn ($record) => $record?->internItems()->exists())
                                            ->validationMessages([
                                                'required' => 'La date de début de stage est obligatoire',
                                            ])
                                            ->required(),
                                        Forms\Components\DatePicker::make('internship_end_date')
                                            ->label('Fin de Stage')
                                            ->live()
                                            ->minDate(fn ($get) => $get('internship_start_date') ? \Carbon\Carbon::parse($get('internship_start_date'))->addWeek() : null)
                                            ->after('internship_start_date')
                                            ->readOnly(fn ($record) => $record?->internItems()->exists())
                                            ->validationMessages([
                                                'after' => 'La date de fin de stage doit être une date postérieure à la date de début de stage',
                                                'required' => 'La date de fin de stage est obligatoire',
                                            ])
                                            ->required(),
                                        Forms\Components\Select::make('status')
                                            ->label('Statut du Stage')
                                            ->options(fn ($get) => collect(StateEnum::cases())
                                                ->filter(fn ($status) => ! ($status === StateEnum::COMPLETED && $get('internship_end_date') >= now()->toDateString()) &&
                                                    ! ($status === StateEnum::IN_PROGRESS && $get('internship_end_date') < now()->toDateString())
                                                )
                                                ->mapWithKeys(fn ($status) => [$status->value => $status->getLabel()])
                                                ->toArray()
                                            )
                                            ->visibleOn(['view'])
                                            ->required()
                                            ->validationMessages([
                                                'required' => 'Le statut du stagiaire est obligatoire',
                                            ])
                                            ->columnSpan('full'),
                                    ])->columns(2),
                            ]),

                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Fieldset::make('Documents de Début de Stage')
                                    ->schema([
                                        Forms\Components\SpatieMediaLibraryFileUpload::make('internship_start_document')
                                            ->hiddenLabel()
                                            ->collection('internship_start_document')
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
                                                'required' => 'Le document de début de stage est obligatoire',
                                                'max_files' => 'Vous ne pouvez pas ajouter plus de 150 fichiers',
                                            ])
                                            ->required()->columnSpan('full'),
                                    ])->columns(2),
                            ])->columnSpan('full'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom et Prénom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('department')
                    ->label('Filière')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grade.name')
                    ->label('Niveau')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('internship_type')
                    ->label('Type de Stage')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->formatStateUsing(fn ($state) => $state instanceof InternshipTypeEnum ? $state->getLabel() : InternshipTypeEnum::from($state)->getLabel()),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('internship_start_date')
                    ->label('Début de Stage')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => match ($record->status) {
                        StateEnum::COMPLETED => 'success',
                        StateEnum::IN_PROGRESS => 'info',
                        StateEnum::STANDBY => 'warning',
                        default => 'gray',
                    })
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('internship_end_date')
                    ->label('Fin de Stage')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => match ($record->status) {
                        StateEnum::COMPLETED => 'success',
                        default => 'gray',
                    })
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('programmed')
                    ->label('Programmé')
                    ->getStateUsing(fn ($record) => $record->internItems()->exists() ? 'Oui' : 'Non')
                    ->badge()
                    ->color(fn ($record) => $record->internItems()->exists() ? 'success' : 'warning')
                    ->toggleable(isToggledHiddenByDefault: false),
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
                Tables\Filters\SelectFilter::make('grade_id')
                    ->label('Niveau')
                    ->relationship('grade', 'name')
                    ->options(fn () => \App\Models\Grade::pluck('name', 'id')->toArray())
                    ->placeholder('Tous'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('activities')
                        ->label('Historiques')
                        ->icon('heroicon-o-clock')
                        ->url(fn ($record) => InternResource::getUrl('activities', ['record' => $record]))
                        ->hidden(fn ($record) => $record->trashed()),
                    Tables\Actions\ViewAction::make()
                        ->hidden(fn ($record) => $record->trashed()),
                    Tables\Actions\EditAction::make()
                        ->hidden(fn ($record) => $record->trashed()),
                    Tables\Actions\DeleteAction::make()
                        ->hidden(fn ($record) => $record->trashed() || $record->internItems()->exists()),
                    Tables\Actions\RestoreAction::make()
                        ->hidden(fn (Intern $intern) => ! $intern->trashed()),
                    Tables\Actions\ForceDeleteAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc')
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            InternItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInterns::route('/'),
            'create' => Pages\CreateIntern::route('/create'),
            'edit' => Pages\EditIntern::route('/{record}/edit'),
            'view' => Pages\ViewIntern::route('/{record}'),
            'activities' => Pages\ListInternActivities::route('/{record}/activities'),
        ];
    }
}
