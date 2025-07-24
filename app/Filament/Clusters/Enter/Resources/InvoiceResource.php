<?php

namespace App\Filament\Clusters\Enter\Resources;

use App\Enums\ProductTypeEnum;
use App\Filament\Clusters\Enter;
use App\Filament\Clusters\Enter\Resources\InvoiceResource\Pages;
use App\Filament\Clusters\Enter\Resources\InvoiceResource\RelationManagers;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $cluster = Enter::class;

    protected static ?int $navigationSort = 1;

    public static function getLabel(): ?string
    {
        return 'Facture';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Factures';
    }

    //    public static function getNavigationBadge(): ?string
    //    {
    //        if (auth()->user()->hasRole('super_admin') || auth()->user()->can('view_all_invoice')) {
    //            return static::getModel()::count();
    //        } else {
    //            return static::getModel()::where('created_by', auth()->id())->count();
    //        }
    //    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where(function ($query) {

            if (auth()->user()?->hasRole('super_admin') || auth()->user()?->can('view_all_invoice')
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
                Forms\Components\Grid::make()
                    ->schema([
                        self::getInvoiceSections(),
                        self::getProductSection(),
                    ]),
            ]);
    }

    private static function getInvoiceSections(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Informations de la Facture')
            ->description('Veuillez renseigner les informations de la facture.')
            ->collapsible()
            ->columns(2)
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Fieldset::make('general')
                                    ->label('Détails de la Facture')
                                    ->schema([
                                        Forms\Components\TextInput::make('invoice_number')
                                            ->label('Numéro de Facture')
                                            ->placeholder('Facture N°')
                                            ->unique(ignoreRecord: true)
                                            ->validationMessages([
                                                'required' => 'Veuillez saisir le numéro de la facture',
                                                'max' => 'Le numéro de la facture ne doit pas dépasser :max caractères',
                                                'unique' => 'Cette facture existe déjà',
                                            ])
                                            ->required(),
                                        Forms\Components\TextInput::make('amount')
                                            ->label('Montant Total')
                                            ->placeholder('Montant Total')
                                            ->numeric()
                                            ->minValue(1)
                                            ->required()
                                            ->validationMessages([
                                                'required' => 'Veuillez saisir le montant total de la facture',
                                                'numeric' => 'Le montant doit être un nombre valide',
                                                'min' => 'Le montant doit être supérieur à 0',
                                            ]),
                                    ])
                                    ->columns(2),
                                Forms\Components\SpatieMediaLibraryFileUpload::make('invoice_doc')
                                    ->columnSpanFull()
                                    ->label('Document de la Facture')
                                    ->collection('invoice_doc')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->downloadable()
                                    ->openable()
                                    ->multiple()
                                /*->validationMessages([
                                        'required' => 'Veuillez télécharger la ou les facture(s) du bon d\'entrée',
                                    ])
                                    ->required()*/,
                                Forms\Components\Select::make('supplier_id')
                                    ->label('Fournisseur')
                                    ->searchable()
                                    ->preload()
                                    ->options(
                                        Supplier::query()
                                            ->where('is_active', 1)
                                            ->pluck('enterprise_name', 'id')
                                    )
                                    ->placeholder('Sélectionnez un fournisseur')
                                    ->createOptionUsing(function ($data) {
                                        $supplier = Supplier::create([
                                            'enterprise_name' => $data['enterprise_name'],
                                            'nif' => $data['nif'],
                                            'full_name' => $data['full_name'],
                                            'phone' => $data['phone'],
                                            'created_by' => auth()->id(),
                                        ]);

                                        return $supplier->id; // Yeni seçeneğin ID'sini döndürür
                                    })
                                    ->createOptionForm(function ($form) {
                                        $form
                                            ->schema([
                                                Forms\Components\TextInput::make('enterprise_name')
                                                    ->label('Nom de l\'Entreprise')
                                                    ->placeholder('Saisissez le nom de l\'entreprise')
                                                    ->required(),
                                                Forms\Components\TextInput::make('nif')
                                                    ->label('NIF')
                                                    ->placeholder('Saisissez le NIF')
                                                    ->unique(ignoreRecord: true)
                                                    ->validationMessages([
                                                        'required' => 'Veuillez saisir le NIF',
                                                        'max' => 'Le NIF ne doit pas dépasser :max caractères',
                                                        'unique' => 'Ce NIF existe déjà',
                                                    ])
                                                    ->required(),
                                                Forms\Components\TextInput::make('full_name')
                                                    ->label('Nom Complet du Fournisseur')
                                                    ->placeholder('Saisissez le nom complet')
                                                    ->required(),
                                                Forms\Components\TextInput::make('phone')
                                                    ->label('Téléphone')
                                                    ->placeholder('Saisissez le numéro de téléphone')
                                                    ->numeric()
                                                    ->unique(
                                                        'suppliers',
                                                        'phone',
                                                        ignoreRecord: true,
                                                        modifyRuleUsing: function ($rule) {
                                                            return $rule->whereNull('deleted_at');
                                                        }
                                                    )
                                                    ->validationMessages([
                                                        'required' => 'Veuillez saisir le numéro de téléphone',
                                                        'max' => 'Le numéro de téléphone ne doit pas dépasser :max caractères',
                                                        'phone.unique' => 'Ce numéro de téléphone existe déjà',
                                                    ])
                                                    ->required(),
                                            ])
                                            ->columns(2);

                                        return $form->model(Supplier::class);
                                    })
                                    ->validationMessages([
                                        'required' => 'Veuillez sélectionner un fournisseur',
                                    ])
                                    ->required(),
                            ]),
                    ]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Select::make('receptionists')
                                    ->label('Receptionnaire(s)')
                                    /*->options(
                                        Employee::query()
                                            ->where('status', 1)
                                            ->get()
                                            ->pluck('full_name', 'id')
                                    )*/
                                    ->options(
                                        Employee::query()
                                            ->where('status', 1)
                                            ->get()
                                            ->pluck('full_name', 'id')
                                    )
                                    ->placeholder('Sélectionnez le(s) receptionnaire(s)')
                                    ->multiple()
                                    ->preload()
                                    ->validationMessages([
                                        'required' => 'Veuillez sélectionner le(s) receptionnaire(s)',
                                    ])
                                    ->required(),
                                Forms\Components\SpatieMediaLibraryFileUpload::make('receipt_doc')
                                    ->columnSpanFull()
                                    ->label('Fiche de Réception du Produit')
                                    ->collection('receipt_doc')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->downloadable()
                                    ->openable()
                                    ->multiple()
                                /*->validationMessages([
                                        'required' => 'Veuillez télécharger la ou les fiche(s) de réception du bon d\'entrée',
                                    ])
                                    ->required()*/,
                                Forms\Components\Toggle::make('invoice_status')
                                    ->label('Etat du Bon d\'Entrée (Definif ou Provisoire)')
                                    ->helperText('Vous devez uniquement desactiver cette option si le bon d\'entrée est provisoire.')
                                    ->onColor('success')
                                    ->offColor('warning')
                                    ->default(true)
                                    ->columnSpanFull(),
                            ])->columns(1),
                    ]),
                Forms\Components\Fieldset::make('invoice_description')
                    ->label('Description de la Facture')
                    ->schema([
                        Forms\Components\Textarea::make('invoice_description')
                            ->hiddenLabel()
                            ->placeholder('Faite une description de la facture')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function getProductSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Informations des Produits Fournis')
            ->description('Veuillez renseigner les informations des produits fournis.')
            ->collapsible()
            ->hiddenOn(['edit', 'view'])
            ->columnSpanFull()
            ->schema([
                Forms\Components\Repeater::make('products')
                    ->relationship('products')
                    ->hiddenLabel()
                    ->addActionLabel('Ajouter un Nouveau Type de Produit')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Type de Produit')
                            ->columnSpanFull()
                            ->placeholder('Sélectionnez un type de produit')
                            ->options(ProductTypeEnum::class)
                            ->validationMessages([
                                'required' => 'Veuillez sélectionner un type de produit',
                            ])
                            ->required()
                            ->live(),

                        Forms\Components\Grid::make()
                            ->visible(fn ($get) => $get('type') == ProductTypeEnum::Electronic->value)
                            ->schema([
                                self::getElectronicFields(),
                            ]),

                        Forms\Components\Grid::make()
                            ->visible(fn ($get) => $get('type') == ProductTypeEnum::Vehicle->value)
                            ->schema([
                                self::getVehicleFields(),
                            ]),

                        Forms\Components\Grid::make()
                            ->visible(fn ($get) => $get('type') == ProductTypeEnum::Other->value)
                            ->schema([
                                self::getOtherFields(),
                            ]),
                    ])
                    ->mutateRelationshipDataBeforeCreateUsing(function ($data) {
                        $data['created_by'] = auth()->id();
                        $data['product_type_id'] = $data['type'];

                        return $data;
                    })
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    private static function getElectronicFields(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Matériels Électroniques Fournis')
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->placeholder('Nom du matériel')
                    ->required(),
                Forms\Components\TextInput::make('brand')
                    ->label('Marque')
                    ->placeholder('Marque du matériel')
                    ->required(),
                Forms\Components\TextInput::make('model')
                    ->label('Modèle')
                    ->placeholder('Modèle du matériel')
                    ->required(),
                Forms\Components\TextInput::make('serial_number')
                    ->label('Numéro de Série')
                    ->placeholder('Numéro de Série du matériel')
                    ->unique(ignoreRecord: true)
                    ->required(),
                Forms\Components\TextInput::make('mac_address')
                    ->label('Adresse MAC')
                    ->placeholder('Adresse MAC du matériel')
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('description')
                    ->label('Description')
                    ->placeholder('Description du matériel')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_available')
                    ->label('Disponibilité')
                    ->hiddenOn(['create', 'edit'])
                    ->helperText('Le produit est-il disponible ?')
                    ->onColor('success')
                    ->offColor('warning')
                    ->default(true),
            ]);
    }

    private static function getVehicleFields(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Véhicules Fournis')
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->placeholder('Nom du véhicule')
                    ->required(),
                Forms\Components\TextInput::make('brand')
                    ->label('Marque')
                    ->placeholder('Marque du véhicule')
                    ->required(),
                Forms\Components\TextInput::make('model')
                    ->label('Modèle')
                    ->placeholder('Modèle du véhicule')
                    ->required(),
                Forms\Components\TextInput::make('plate_number')
                    ->label('Numéro de Plaque')
                    ->placeholder('Numéro de Plaque du véhicule')
                    ->unique(ignoreRecord: true)
                    ->required(),
                Forms\Components\TextInput::make('chassis_number')
                    ->label('Numéro de Chassis')
                    ->placeholder('Numéro de Chassis du véhicule')
                    ->unique(ignoreRecord: true)
                    ->required(),
                Forms\Components\TextInput::make('description')
                    ->label('Description')
                    ->placeholder('Description du véhicule')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_available')
                    ->label('Disponibilité')
                    ->hiddenOn(['create', 'edit'])
                    ->helperText('Le produit est-il disponible ?')
                    ->onColor('success')
                    ->offColor('warning')
                    ->default(true),
            ]);
    }

    private static function getOtherFields(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Vivres et Autres Produits Fournis')
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->placeholder('Nom du produit')
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->label('Quantité')
                    ->placeholder('Quantité du produit')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('description')
                    ->label('Description')
                    ->placeholder('Description du produit')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_available')
                    ->label('Disponibilité')
                    ->hiddenOn(['create', 'edit'])
                    ->helperText('Le produit est-il disponible ?')
                    ->onColor('success')
                    ->offColor('warning')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        // dd(static::getEloquentQuery()->get());
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Facture')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier.full_name')
                    ->label('Fournisseur')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice_status')
                    ->label('Etat de la Fourtniture')
                    ->badge()
                    ->color(fn ($record) => match ($record->invoice_status->value) {
                        0 => 'warning',
                        1 => 'success',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Montant Total')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ',') . ' FCFA')
                    ->sortable(),
                Tables\Columns\TextColumn::make('products_count')
                    ->label('Produits')
                    ->badge()
                    ->color('info')
                    ->counts('products')
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
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->hidden(fn ($record) => $record->trashed()),
                    Tables\Actions\EditAction::make()
                        ->hidden(fn ($record) => $record->trashed()),
                    Tables\Actions\DeleteAction::make()
                        ->hidden(fn ($record) => $record->products()->exists() || $record->trashed()),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ForceDeleteAction::make()
                        ->visible(fn ($record) => $record->trashed() && auth()->user()->hasRole('super_admin')),
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
            RelationManagers\ProductsRelationManager::class,
            RelationManagers\PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
            'view' => Pages\ViewInvoice::route('/{record}'),
        ];
    }
}
