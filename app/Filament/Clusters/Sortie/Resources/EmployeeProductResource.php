<?php

namespace App\Filament\Clusters\Sortie\Resources;

use App\Enums\ProductTypeEnum;
use App\Filament\Clusters\Sortie;
use App\Filament\Clusters\Sortie\Resources\EmployeeProductResource\Pages;
use App\Filament\Clusters\Sortie\Resources\EmployeeProductResource\RelationManagers;
use App\Models\Employee;
use App\Models\EmployeeProduct;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EmployeeProductResource extends Resource
{
    protected static ?string $model = EmployeeProduct::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-right';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Bons de Sortie';

    protected static ?string $cluster = Sortie::class;

    // protected static ?string $navigationGroup = 'Gestion des Matériels';

    public static function getLabel(): ?string
    {
        return 'Bon de Sortie';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Bons de Sortie';
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        if (auth()->user()->hasRole('super_admin') || auth()->user()->can('view_all_employee_product')) {
            return parent::getEloquentQuery();
        } else {
            return parent::getEloquentQuery()->where('created_by', auth()->id());
        }
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                self::getInvoiceSection(),
                                self::getProductSection(),
                            ]),
                    ]),
            ]);
    }

    private static function getInvoiceSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Informations du Bon de Sortie')
            ->description('Veuillez renseigner les détails du bon de sortie')
            ->columns(2)
            ->schema([
                Forms\Components\TextInput::make('product_out_doc_number')
                    ->label('Numéro de Bon de Sortie')
                    ->placeholder('Numéro de Bon de Sortie')
                    ->validationMessages([
                        'required' => 'Veuillez renseigner le numéro de bon de sortie',
                    ])
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => 'Le numéro de bon de sortie existe déjà',
                        'required' => 'Veuillez renseigner le numéro de bon de sortie',
                    ])
                    ->required(),
                Forms\Components\SpatieMediaLibraryFileUpload::make('product_out_doc')
                    ->label('Document de Sortie')
                    ->collection('product_out_doc')
                    ->acceptedFileTypes(['application/pdf'])
                    ->downloadable()
                    ->openable()
                    ->placeholder('Veuillez télécharger le document de sortie'),
                Forms\Components\Fieldset::make('description')
                    ->label('Description du Bon de Sortie')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->hiddenLabel()
                            ->placeholder('Faites une description du bon de sortie')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Toggle::make('status')
                    ->hiddenOn(['create'])
                    ->label('Statut du Bon de Sortie')
                    ->helperText('Vous devez uniquement desactiver cette option si les produits ne sont pas definitivement attribués')
                    ->onColor('success')
                    ->offColor('warning')
                    ->default(true)
                    ->columnSpanFull(),
            ]);
    }

    private static function getProductSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Attribution de Produits aux Personnels')
            ->description('Veuillez attribuer des produits aux personnels')
            ->hiddenOn(['edit', 'view'])
            ->columnSpanFull()
            ->schema([
                Forms\Components\Repeater::make('employeeProductItems')
                    ->relationship('employeeProductItems')
                    ->hiddenLabel()
                    ->addActionLabel('Faite une autre attribution')
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

                        // update the status of the product to unavailable if the product_type_id not equal to 3
                        $productId = $data['product_id'];
                        $product = Product::find($productId);
                        $productTypeId = $product->product_type_id->value;

                        if ($productTypeId != 3) {
                            $product->update([
                                'is_available' => false,
                            ]);
                        }

                        // if the product type is other update the product quantity

                        if ($productTypeId == 3) {
                            $productQuantity = $product->quantity;
                            $dataQuantity = $data['quantity'];

                            if ($dataQuantity > $productQuantity) {
                                abort(403, 'La quantité demandée est supérieure à la quantité disponible. Quantité demandée: '.$dataQuantity.', Quantité disponible: '.$productQuantity);
                            }

                            $newProductQuantity = $productQuantity - $dataQuantity;

                            $product->update([
                                'quantity' => $newProductQuantity,
                            ]);
                        }

                        $data['product_type_id'] = $productTypeId;

                        return $data;
                    })
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    private static function getElectronicFields(): Forms\Components\Section
    {
        return Forms\Components\Section::make()
            ->schema([
                Forms\Components\Fieldset::make('Matériels Électroniques')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Personnel')
                            ->placeholder('Sélectionnez un personnel')
                            ->options(Employee::query()
                                ->where('status', true)
                                ->get()->pluck('full_name', 'id')
                            )
                            ->validationMessages([
                                'required' => 'Veuillez sélectionner un personnel',
                            ])
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('product_id')
                            ->label('Produit')
                            ->placeholder('Sélectionnez un produit')
                            ->options(Product::query()
                                ->where('product_type_id', ProductTypeEnum::Electronic)
                                ->where('is_available', true)
                                ->get()->pluck('name', 'id')
                            )
                            // ->relationship('product', 'name')
                            ->validationMessages([
                                'required' => 'Veuillez sélectionner un produit',
                            ])
                            ->searchable()
                            ->preload()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->required(),
                    ]),
                Forms\Components\Fieldset::make('Description')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->hiddenLabel()
                            ->placeholder('Description du produit')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function getVehicleFields(): Forms\Components\Section
    {
        return Forms\Components\Section::make()
            ->schema([
                Forms\Components\Fieldset::make('Véhicules')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Personnel')
                            ->placeholder('Sélectionnez un personnel')
                            ->options(Employee::query()
                                ->where('status', true)
                                ->get()->pluck('full_name', 'id')
                            )
                            // ->options(Employee::where('status', true)->get()->pluck('full_name', 'id'))
                            ->validationMessages([
                                'required' => 'Veuillez sélectionner un personnel',
                            ])
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('product_id')
                            ->label('Produit')
                            ->placeholder('Sélectionnez un produit')
                            ->options(Product::query()
                                ->where('product_type_id', ProductTypeEnum::Vehicle)
                                ->where('is_available', true)
                                ->get()->pluck('name', 'id')
                            )
                            ->validationMessages([
                                'required' => 'Veuillez sélectionner un produit',
                            ])
                            ->searchable()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->required(),
                    ]),
                Forms\Components\Fieldset::make('Description')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->hiddenLabel()
                            ->placeholder('Description du produit')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function getOtherFields(): Forms\Components\Section
    {
        return Forms\Components\Section::make()
            ->schema([
                Forms\Components\Fieldset::make('Autres Produits')
                    ->columns(3)
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Personnel')
                            ->placeholder('Sélectionnez un personnel')
                            ->options(Employee::query()
                                ->where('status', true)
                                ->get()->pluck('full_name', 'id')
                            )
                            ->validationMessages([
                                'required' => 'Veuillez sélectionner un personnel',
                            ])
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('product_id')
                            ->label('Produit')
                            ->placeholder('Sélectionnez un produit')
                            ->options(Product::query()
                                ->where('product_type_id', ProductTypeEnum::Other)
                                ->where('quantity', '>', 0)->where('is_available', true)
                                ->get()->pluck('name', 'id')
                            )
                            ->validationMessages([
                                'required' => 'Veuillez sélectionner un produit',
                            ])
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('quantity')
                            ->label('Quantité')
                            ->placeholder('Quantité du produit')
                            ->options(function (callable $get) {
                                $productId = $get('product_id');
                                $product = Product::find($productId);

                                if (! $product) {
                                    return [];
                                }

                                return array_combine(range(1, $product->quantity), range(1, $product->quantity));
                            })
                            ->validationMessages([
                                'required' => 'Veuillez entrer une quantité',
                                'numeric' => 'La quantité doit être un nombre',
                                'min' => 'La quantité doit être supérieure à 0',
                            ])
                            ->searchable()
                            ->required(),
                    ]),
                Forms\Components\Fieldset::make('Description')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->hiddenLabel()
                            ->placeholder('Description du produit')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        // dd(static::getEloquentQuery()->get());
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product_out_doc_number')
                    ->label('Numéro deSortie')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn ($record) => match ($record->status->value) {
                        0 => 'warning',
                        1 => 'success',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('createdBy.full_name')
                    ->label('Créé par')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date de Création')
                    ->date('d/m/Y')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make(
                    [
                        Tables\Actions\ViewAction::make(),
                        Tables\Actions\EditAction::make(),
                        Tables\Actions\DeleteAction::make()
                            ->visible(fn (Model $record) => $record->employeeProductItems->isEmpty()),
                    ]
                ),
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
            RelationManagers\EmployeeProductItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeeProducts::route('/'),
            'create' => Pages\CreateEmployeeProduct::route('/create'),
            'edit' => Pages\EditEmployeeProduct::route('/{record}/edit'),
            'view' => Pages\ViewEmployeeProduct::route('/{record}'),
        ];
    }
}
