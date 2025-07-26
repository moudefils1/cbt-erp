<?php

namespace App\Filament\Resources;

use App\Enums\EchelonEnum;
use App\Enums\EmployeeStatusEnum;
use App\Enums\EmployeeTypeEnum;
use App\Enums\GenderEnum;
use App\Enums\GridCategoryEnum;
use App\Enums\MaritalStatusEnum;
use App\Filament\Exports\EmployeeExporter;
use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\Employee;
use App\Models\Location;
use App\Models\Product;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Gestion des Personnels';

    protected static ?int $navigationSort = 9;

    public static function getLabel(): ?string
    {
        return 'Personnel';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Personnels';
    }

    public static function getNavigationBadge(): ?string
    {
        if (auth()->user()->hasRole('super_admin') || auth()->user()->can('view_all_employee')) {
            return static::getModel()::count();
        } else {
            return static::getModel()::where('created_by', auth()->id())->count();
        }
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where(function ($query) {

            if (auth()->user()?->hasRole('super_admin') || auth()->user()?->can('view_all_employee')
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
                                    ->placeholder('Nom du personnel')
                                    ->validationMessages([
                                        'required' => 'Le nom du personnel est obligatoire',
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('surname')
                                    ->label('Prénoms')
                                    ->placeholder('Prénoms du personnel')
                                    ->validationMessages([
                                        'required' => 'Les prénoms du personnel sont obligatoires',
                                    ])
                                    ->required(),
                                Forms\Components\Select::make('country_id')
                                    ->label('Pays')
                                    ->placeholder('Sélectionnez un pays')
                                    ->options(\App\Models\Country::pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->validationMessages([
                                        'required' => 'Le pays du personnel est obligatoire',
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('birth_place')
                                    ->label('Lieu de Naissance')
                                    ->placeholder('Lieu de naissance du personnel')
                                    ->default('N\'Djaména')
                                    ->maxLength(100)
                                    ->validationMessages([
                                        'required' => 'Le lieu de naissance du personnel est obligatoire',
                                    ])
                                    ->required(),
                                Forms\Components\DatePicker::make('birth_date')
                                    ->label('Date de Naissance')
                                    ->placeholder('Sélectionnez une date')
                                    ->validationMessages([
                                        'required' => 'La date de naissance du personnel est obligatoire',
                                    ])
                                    ->required(),
                                Forms\Components\Select::make('gender')
                                    ->label('Genre')
                                    ->placeholder('Sélectionnez un genre')
                                    ->options([
                                        1 => 'Homme',
                                        2 => 'Femme',
                                    ])
                                    ->validationMessages([
                                        'required' => 'Veillez sélectionner un genre',
                                    ])
                                    ->required(),
                                Forms\Components\Select::make('marital_status')
                                    ->label('Situation Matrimoniale')
                                    ->placeholder('Sélectionnez une situation')
                                    ->options(MaritalStatusEnum::class)
                                    ->default(MaritalStatusEnum::SINGLE->value)
                                    ->required(),
                                Forms\Components\TextInput::make('children_count')
                                    ->label('Nombre d\'Enfants')
                                    ->placeholder('Nombre d\'enfants du personnel')
                                    ->default(0)
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('phone')
                                    ->label('Téléphone')
                                    ->maxLength(20)
                                    ->placeholder('Téléphone du personnel')
                                    ->numeric()
                                    ->unique(
                                        'employees',
                                        'phone',
                                        ignoreRecord: true,
                                        modifyRuleUsing: function ($rule) {
                                            return $rule->whereNull('deleted_at');
                                        }
                                    )
                                    ->minLength(8)
                                    ->maxLength(11)
                                    ->validationMessages([
                                        'phone.unique' => 'Ce numéro de téléphone existe déjà',
                                    ]),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->placeholder('Email du personnel')
                                    ->email()
                                    ->unique(
                                        'employees',
                                        'email',
                                        ignoreRecord: true,
                                        modifyRuleUsing: function ($rule) {
                                            return $rule->whereNull('deleted_at');
                                        }
                                    )
                                    ->validationMessages([
                                        'email.unique' => 'Cet email existe déjà',
                                    ])
                                    ->maxLength(50),
                                Forms\Components\TextInput::make('nni')
                                    ->label('NNI')
                                    ->numeric()
                                    ->maxLength(11)
                                    ->placeholder('NNI du personnel')
                                    ->unique(
                                        'employees',
                                        'nni',
                                        ignoreRecord: true,
                                        modifyRuleUsing: function ($rule) {
                                            return $rule->whereNull('deleted_at');
                                        }
                                    ),
                            ])->columns(2),
                        Forms\Components\Fieldset::make('Informations Professionnelles')
                            ->schema([
                                Forms\Components\Select::make('location_id')
                                    ->label('Département')
                                    ->placeholder('Sélectionnez un département')
//                                    ->options(fn () => Location::whereHas('employees') // Sadece çalışanı olan locationları getir
//                                    ->pluck('name', 'id')
//                                        ->toArray()
//                                    )
                                    ->options(Location::pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(fn (callable $set) => $set('task_id', null))
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()

//                                    ->options(Location::pluck('name', 'id'))
//                                    ->searchable()
//                                    ->preload()
//                                    ->reactive()
                                    ->when(auth()->user()->hasRole('super_admin') || auth()->user()->can('create_custom_location', Location::class), fn ($select) => $select->createOptionForm(function ($form) {
                                        $form
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Nom')
                                                    ->required(),
                                            ]);

                                        return $form->model(\App\Models\Location::class);
                                    })->createOptionUsing(function ($data) {
                                        $location = \App\Models\Location::create([
                                            'name' => $data['name'],
                                            'created_by' => auth()->id(),
                                        ]);

                                        return $location->id;
                                    })
                                    )
                                    ->afterStateUpdated(fn (callable $set) => $set('task_id', null))
                                    ->validationMessages([
                                        'required' => 'Veillez sélectionner un département',
                                    ])
                                    ->required(),
                                Forms\Components\Select::make('task_id')
                                    ->label('Poste')
                                    ->placeholder('Sélectionnez un poste')
                                    ->relationship('task', 'name')
                                    ->options(function (callable $get) {
                                        $locationId = $get('location_id');

                                        if (! $locationId) {
                                            return [];
                                        }

                                        return Task::where('location_id', $locationId)
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    // ->options(Task::pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->when(auth()->user()->hasRole('super_admin') || auth()->user()->can('create_custom_task', Task::class), fn ($select) => $select->createOptionForm(function ($form) {
                                        $form
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Nom du Poste')
                                                    ->required(),
                                                Forms\Components\Select::make('location_id')
                                                    ->label('Département du Poste')
                                                    ->relationship('location', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required(),
                                            ])->columns(2);

                                        return $form->model(\App\Models\Task::class);
                                    })->createOptionUsing(function ($data) {
                                        $task = \App\Models\Task::create([
                                            'name' => $data['name'],
                                            'location_id' => $data['location_id'],
                                            'created_by' => auth()->id(),
                                        ]);

                                        return $task->id;
                                    })
                                    )
                                    ->validationMessages([
                                        'required' => 'Veillez sélectionner un poste',
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('matricule')
                                    ->label('Matricule')
                                    ->placeholder('Matricule du personnel')
                                    ->maxLength(20)
                                    ->unique(
                                        'employees',
                                        'matricule',
                                        ignoreRecord: true,
                                        modifyRuleUsing: function ($rule) {
                                            return $rule->whereNull('deleted_at');
                                        }
                                    )
                                    ->validationMessages([
                                        'required' => 'Le matricule du personnel est obligatoire',
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('cnps_no')
                                    ->label('Numéro CNPS')
                                    ->maxLength(50)
                                    ->placeholder('Numéro CNPS du personnel')
                                    ->unique(
                                        'employees',
                                        'cnps_no',
                                        ignoreRecord: true,
                                        modifyRuleUsing: function ($rule) {
                                            return $rule->whereNull('deleted_at');
                                        }
                                    ),
                                Forms\Components\Fieldset::make('Type du Personnel')
                                    ->schema([
                                        Forms\Components\Select::make('employee_type_id')
                                            ->options(EmployeeTypeEnum::class)
                                            ->label('Type du Personnel')
                                            ->placeholder('Sélectionnez un type')
                                            ->searchable()
                                            ->preload()
                                            ->validationMessages([
                                                'required' => 'Veillez sélectionner un type de personnel',
                                            ])
                                            ->live()
                                            ->afterStateUpdated(fn (callable $set) => $set('status', null))
                                            ->required(),
                                        Forms\Components\Select::make('grade_id')
                                            ->label("Niveau d'Étude")
                                            ->placeholder('Sélectionnez un grade')
                                            ->options(\App\Models\Grade::pluck('name', 'id'))
                                            ->searchable()
                                            ->preload()
                                            ->when(auth()->user()->hasRole('super_admin') || auth()->user()->can('create_custom_grade', \App\Models\Grade::class), fn ($select) => $select->createOptionForm(function ($form) {
                                                $form
                                                    ->schema([
                                                        Forms\Components\TextInput::make('name')
                                                            ->label('Niveau d\'Étude')
                                                            ->placeholder('Ex: Licence, Master, Doctorat etc.')
                                                            ->validationMessages([
                                                                'required' => 'Le niveau d\'étude du personnel est obligatoire',
                                                            ])
                                                            ->required(),
                                                        Forms\Components\Fieldset::make('Description')
                                                            ->schema([
                                                                Forms\Components\Textarea::make('description')
                                                                    ->hiddenLabel()
                                                                    ->placeholder('Description du niveau d\'étude')
                                                                    ->columnSpanFull(),
                                                            ]),
                                                    ]);

                                                return $form->model(\App\Models\Grade::class);
                                            })->createOptionUsing(function ($data) {
                                                $grade = \App\Models\Grade::create([
                                                    'name' => $data['name'],
                                                    'description' => $data['description'],
                                                    'created_by' => auth()->id(),
                                                ]);

                                                return $grade->id;
                                            })
                                            )
                                            ->validationMessages([
                                                'required' => 'Veillez sélectionner un grade',
                                            ])
                                            ->required(),
                                        Forms\Components\DatePicker::make('hiring_date')
                                            ->readOnly(fn ($get, $record) => $get('status') == EmployeeStatusEnum::ON_LEAVE->value || ($record && $record->employeePositions()->exists())
                                            )
                                            ->label('Début (Fonction/Contrat)')
                                            ->placeholder('Sélectionnez une date')
                                            ->validationMessages([
                                                'required' => 'La date de début du fonction/contrat est obligatoire',
                                            ])
                                            ->live()
                                            ->afterStateUpdated(fn (callable $set) => [
                                                $set('status', null),
                                                $set('end_date', null),
                                            ])
                                            ->required(),
                                        Forms\Components\DatePicker::make('end_date')
                                            ->readOnly(fn ($get, $record) => $get('status') == EmployeeStatusEnum::ON_LEAVE->value || ($record && $record->employeePositions()->exists())
                                            )
                                            ->label('Fin (Fonction/Contrat)')
                                            ->placeholder('Sélectionnez une date')
                                            ->after('hiring_date')
                                            ->visible(fn ($get) => $get('employee_type_id') == EmployeeTypeEnum::CDD->value)
                                            ->validationMessages([
                                                'required' => 'La date de fin du fonction/contrat est obligatoire',
                                                'after' => 'La date de fin du fonction/contrat doit être postérieure à la date de début',
                                            ])
                                            ->minDate(fn ($get) => $get('hiring_date') ? \Carbon\Carbon::parse($get('hiring_date'))->addMonths(6) : null)
                                            ->live()
                                            ->afterStateUpdated(fn (callable $set) => $set('status', null))
                                            ->required(),
                                    ])->columns(2),
//                                Forms\Components\Fieldset::make('Grille de Salaire')
//                                    ->visible(fn () => auth()->user()->hasRole('super_admin') || auth()->user()->can('view_basic_salary'))
//                                    ->schema([
//                                        Forms\Components\Select::make('grid_category_id')
//                                            ->label('Catégorie de Grille')
//                                            ->placeholder('Sélectionnez une catégorie')
//                                            ->options(GridCategoryEnum::class)
//                                            ->searchable()
//                                            ->preload()
//                                            ->validationMessages([
//                                                'required' => 'Veillez sélectionner une catégorie de grille',
//                                            ])
//                                            ->required(),
//                                        Forms\Components\Select::make('echelon_id')
//                                            ->label('Échelon')
//                                            ->placeholder('Sélectionnez un échelon')
//                                            ->options(EchelonEnum::class)
//                                            ->searchable()
//                                            ->preload()
//                                            ->validationMessages([
//                                                'required' => 'Veillez sélectionner un échelon',
//                                            ])
//                                            ->required(),
//                                        Forms\Components\TextInput::make('basic_salary')
//                                            ->label('Salaire de Base')
//                                            ->placeholder('Salaire de base du personnel')
//                                            ->numeric()
//                                            ->minValue(1)
//                                            ->validationMessages([
//                                                'required' => 'Le salaire de base du personnel est obligatoire',
//                                                'min' => 'Le salaire de base du personnel doit être supérieur à 0',
//                                            ])
//                                            ->required(),
//                                    ])
//                                    ->columns(3),
                                Forms\Components\Fieldset::make('Statut du Personnel')
                                    ->hidden(fn ($get) => $get('status') == EmployeeStatusEnum::ON_LEAVE->value || $get('status') == EmployeeStatusEnum::IN_TRAINING->value)
                                    ->schema([
                                        Forms\Components\Select::make('status')
                                            ->label('Actuellement')
                                            ->placeholder('Sélectionnez un statut')
                                            ->searchable()
                                            ->preload()
                                            ->options(function ($get) {
                                                $options = array_filter(EmployeeStatusEnum::cases(), fn ($status) => $status != EmployeeStatusEnum::ON_LEAVE && $status != EmployeeStatusEnum::IN_TRAINING);

                                                if ($get('employee_type_id') != null && $get('employee_type_id') == EmployeeTypeEnum::CDD->value) {

                                                    if ($get('end_date') >= now()->format('Y-m-d')) {
                                                        $options = array_filter($options, fn ($status) => $status == EmployeeStatusEnum::WORKING);
                                                    } else {
                                                        $options = array_filter($options, fn ($status) => $status != EmployeeStatusEnum::WORKING);
                                                    }
                                                }

                                                //                                                else {
                                                //                                                    if ($get('hiring_date') != null && $get('hiring_date') >= now()->format('Y-m-d')) {
                                                //                                                        $options = array_filter($options, fn ($status) => $status == EmployeeStatusEnum::WORKING);
                                                //                                                        } else {
                                                //                                                        $options = array_filter($options, fn($status) => $status != EmployeeStatusEnum::WORKING);
                                                //                                                    }
                                                //                                                }

                                                $data = collect($options)
                                                    ->mapWithKeys(fn ($status) => [$status->value => $status->getLabel()])
                                                    ->toArray();

                                                return $data;
                                            })
                                            ->required()
                                            ->validationMessages([
                                                'required' => 'Veillez sélectionner un statut',
                                            ])
                                            ->live(),
                                        Forms\Components\Fieldset::make('Détails du Statut')
                                            ->hidden(fn ($get) => $get('status') == EmployeeStatusEnum::WORKING->value || $get('status') == null)
                                            ->schema([
                                                //                                                Forms\Components\DatePicker::make('status_start_date')
                                                //                                                    ->label('Date de Début de l\'Événement')
                                                //                                                    ->placeholder('Sélectionnez une date')
                                                //                                                    ->rules(fn ($get) => array_filter([
                                                //                                                        'required',
                                                //                                                        'before_or_equal:today',
                                                //                                                        'after:hiring_date',
                                                //                                                        $get('employee_type_id') == EmployeeTypeEnum::CDD->value && $get('end_date')
                                                //                                                            ? 'after:end_date' // Eğer çalışan CDD ise ve end_date doluysa after:end_date uygulanır
                                                //                                                            : null,
                                                //                                                    ]))
                                                //                                                    ->validationMessages([
                                                //                                                        'required' => 'La date de début est obligatoire',
                                                //                                                        'before_or_equal' => 'La date de début doit être antérieure ou égale à la date d\'aujourd\'hui',
                                                //                                                        'after' => 'La date de début doit être postérieure à la date de début du Fonction/Contrat',
                                                //                                                    ])
                                                //                                                    ->visible(fn ($get) => $get('status') != EmployeeStatusEnum::WORKING->value),
                                                Forms\Components\Textarea::make('status_comment')
                                                    ->label('Description du Statut')
                                                    ->placeholder("Description de l'événement: raison, commentaire, etc.")
                                                    ->columnSpanFull()
                                                    ->visible(fn ($get) => $get('status') != EmployeeStatusEnum::WORKING->value),
                                                Forms\Components\SpatieMediaLibraryFileUpload::make('employee_status_item_documents')
                                                    ->visible(fn ($get) => $get('status') != EmployeeStatusEnum::WORKING->value)
                                                    ->label('Documents Relatifs au Statut')
                                                    ->collection('employee_status_item_documents')
                                                    ->multiple()
                                                    ->downloadable()
                                                    ->openable()
                                                    ->acceptedFileTypes([
                                                        'application/pdf',
                                                        'text/csv',
                                                        'text/xls',
                                                        'application/vnd.ms-excel',
                                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                                    ])
                                                    ->maxFiles(150)
                                                    ->maxSize(10240)
                                                    ->helperText('Le fichier doit être en format PDF, XLS ou CSV et ne doit pas dépasser 10 Mo')
                                                    ->validationMessages([
                                                        'employee_status_item_documents.max_files' => 'Vous ne pouvez pas télécharger plus de 150 fichiers',
                                                        'employee_status_item_documents.acceptedTypes' => 'Le fichier doit être de type PDF, XLS ou CSV',
                                                        'employee_status_item_documents.max_size' => 'Le fichier ne doit pas dépasser 10 Mo',
                                                    ])->columnSpanFull(),
                                            ])->columns(2),
                                    ])
                                    ->columns(1),
                                Forms\Components\Fieldset::make('Documents Liés au Personnel')
                                    ->schema([
                                        Forms\Components\SpatieMediaLibraryFileUpload::make('employee_documents')
                                            ->hiddenLabel()
                                            ->collection('employee_documents')
                                            ->multiple()
                                            ->downloadable()
                                            ->openable()
                                            ->maxSize(10240)
                                            ->maxFiles(150)
                                            ->helperText('Le fichier doit être en format PDF, XLS ou CSV et ne doit pas dépasser 10 Mo')
                                            ->acceptedFileTypes(
                                                [
                                                    'application/pdf',
                                                    'text/xls',
                                                    'image/*',
                                                ]
                                            )
                                            ->maxFiles(150)
                                            ->validationMessages([
                                                'employee_documents.max_files' => 'Vous ne pouvez pas télécharger plus de 150 fichiers',
                                                'employee_documents.acceptedTypes' => 'Le fichier doit être de type PDF, XLS ou CSV',
                                                'employee_documents.max_size' => 'Le fichier ne doit pas dépasser 10 Mo',
                                            ]),
                                    ])->columns(1),
                            ])->columns(2),
                        Forms\Components\Fieldset::make('Personne à Contacter en Cas d\'Urgence')
                            ->schema([
                                Forms\Components\TextInput::make('emergency_contact_name')
                                    ->label('Nom')
                                    ->placeholder('Nom de la personne à contacter')
                                    ->maxLength(100),
                                Forms\Components\TextInput::make('emergency_contact_phone')
                                    ->label('Téléphone')
                                    ->placeholder('Téléphone de la personne à contacter')
                                    ->numeric()
                                    ->minLength(8)
                                    ->maxLength(11)
                                    ->validationMessages([
                                        'numeric' => 'Le numéro de téléphone doit être numérique',
                                        'min' => 'Le numéro de téléphone doit contenir au moins 8 chiffres',
                                        'max' => 'Le numéro de téléphone ne doit pas dépasser 11 chiffres',
                                    ]),
                                Forms\Components\TextInput::make('emergency_contact_relationship')
                                    ->label('Lien')
                                    ->placeholder('Lien avec la personne à contacter')
                                    ->maxLength(100),
                            ])->columns(3),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        // dd(static::getEloquentQuery()->get());
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('surname')
                    ->label('Prénoms')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Département')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('task.name')
                    ->label('Poste')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee_type_id')
                    ->label('Type de Personnel')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Actuellement')
                    ->badge(),
                Tables\Columns\TextColumn::make('employeeProductItems_count')
                    ->visible(fn () => auth()->user()->can('viewAny', Product::class))
                    ->label('Produits')
                    ->getStateUsing(fn ($record) => $record->employeeProductItems->sum('product_count'))
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('internItems_count')
                    ->label('Stagiaires')
                    ->getStateUsing(fn ($record) => $record->internItems->count())
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('employeePositions_count')
                    ->label('Postes Occupés')
                    ->getStateUsing(fn ($record) => $record->employeePositions->count())
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Filters\SelectFilter::make('gender')
                    ->label('Genre')
                    ->options(GenderEnum::class)
                    ->preload(),
                Tables\Filters\SelectFilter::make('location_id')
                    ->label('Département')
                    ->options(Location::pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('employee_type_id')
                    ->label('Type de Personnel')
                    ->options(EmployeeTypeEnum::class)
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Actuellement')
                    ->options(EmployeeStatusEnum::class)
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(EmployeeExporter::class)
                    ->label('Exporter')
                    ->modalHeading('Exporter les Perso
                    nnels')
                    ->icon('heroicon-o-arrow-down-tray')
                    // visible if data exists
                    ->visible(fn () => static::getModel()::count() > 0)
                /* ->columnMapping(false) */,
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('activities')
                        ->label('Historiques')
                        ->icon('heroicon-o-clock')
                        ->url(fn ($record) => EmployeeResource::getUrl('activities', ['record' => $record]))
                        ->hidden(fn ($record) => $record->trashed()),
                    Tables\Actions\ViewAction::make()
                        ->hidden(fn ($record) => $record->trashed()),
                    Tables\Actions\EditAction::make()
                        ->hidden(fn ($record) => $record->trashed()),
                    Tables\Actions\DeleteAction::make()
                        ->hidden(fn ($record) => $record->employeeProductItems()->exists() || $record->internItems()->exists() || $record->employeePositions()->exists() || $record->leaves()->exists() || $record->debts()->exists() || $record->employeeLeaveBalances()->exists() || $record->trashed()),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            'employeeLeaveBalances' => RelationManagers\EmployeeLeaveBalancesRelationManager::class,
            'leaves' => RelationManagers\LeavesRelationManager::class,
            //'salaryBonuses' => RelationManagers\SalaryBonusesRelationManager::class,
            //'debts' => RelationManagers\DebtsRelationManager::class,
            //'debtItems' => RelationManagers\DebtItemsRelationManager::class,
            'employeeProductItems' => RelationManagers\EmployeeProductItemsRelationManager::class,
            'employeePositions' => RelationManagers\EmployeePositionsRelationManager::class,
            'internItems' => RelationManagers\EmployeeInternItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
            'view' => Pages\ViewEmployee::route('/{record}'),
            'activities' => Pages\ListEmployeeActivities::route('/{record}/activities'),
        ];
    }
}
