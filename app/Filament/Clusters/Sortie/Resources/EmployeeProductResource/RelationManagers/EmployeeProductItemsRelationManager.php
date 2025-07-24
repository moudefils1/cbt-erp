<?php

namespace App\Filament\Clusters\Sortie\Resources\EmployeeProductResource\RelationManagers;

use App\Enums\ProductStatusEnum;
use App\Enums\ProductTypeEnum;
use App\Models\Employee;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EmployeeProductItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'employeeProductItems';

    protected static ?string $title = 'Liste des Bénéficiaires';

    protected static ?string $label = 'Bénéficiaires';

    public function form(Form $form): Form
    {
        return $form

            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\Select::make('product_type_id')
                            ->label('Type de Produit')
                            ->hiddenOn(['view', 'edit'])
                            ->columnSpanFull()
                            ->placeholder('Sélectionnez un type de produit')
                            ->options(ProductTypeEnum::class)
                            ->validationMessages([
                                'required' => 'Veuillez sélectionner un type de produit',
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\Grid::make()
                            ->visible(fn ($get) => $get('product_type_id') == ProductTypeEnum::Electronic->value)
                            ->disabled(fn ($get) => ! $get('is_active'))
                            ->schema([
                                self::getElectronicFields(),
                            ]),
                        Forms\Components\Grid::make()
                            ->visible(fn ($get) => $get('product_type_id') == ProductTypeEnum::Vehicle->value)
                            ->disabled(fn ($get) => ! $get('is_active'))
                            ->schema([
                                self::getVehicleFields(),
                            ]),
                        Forms\Components\Grid::make()
                            ->visible(fn ($get) => $get('product_type_id') == ProductTypeEnum::Other->value)
                            ->disabled(fn ($get) => ! $get('is_active'))
                            ->schema([
                                self::getOtherFields(),
                            ]),
                        Forms\Components\Toggle::make('is_active')
                            ->hiddenOn('create')
                            ->label('Etat du Produit')
                            ->helperText('Verte: En cour d\'utilisation. Rouge: Rémis/Réformé etc.')
                            ->onColor('success')
                            ->offColor('danger')
                            ->reactive()
                            ->default(true),
                        Forms\Components\Fieldset::make('Etat Actuel du Produit')
                            ->visible(fn ($get) => ! $get('is_active'))
                            ->columns(2)
                            ->schema([
                                Forms\Components\Section::make()
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\Select::make('state')
                                            ->label('Etat')
                                            ->placeholder('Sélectionnez un état')
                                            ->options(function ($record) {
                                                if ($record->product_type_id->value == 3) {
                                                    return [
                                                        \App\Enums\ProductStatusEnum::Restored->value => \App\Enums\ProductStatusEnum::Restored->getLabel(),
                                                    ];
                                                }

                                                return array_map(fn ($enum) => $enum->getLabel(), \App\Enums\ProductStatusEnum::cases());
                                            })
                                            // ->options(ProductStatusEnum::class)
                                            ->rules([
                                                'required_if:is_active,false',
                                            ])
                                            ->validationMessages([
                                                'required_if' => 'Veuillez sélectionner un état',
                                                'required' => 'Veuillez sélectionner un état',
                                            ])
                                            ->reactive()
                                            ->required(),
                                        Forms\Components\Select::make('state_quantity')
                                            ->label(function ($get) {
                                                $state = $get('state');
                                                if ($state == null) {
                                                    return 'Quantité';
                                                } elseif ($state == ProductStatusEnum::Restored->value) {
                                                    return 'Quantité Restituée';
                                                } elseif ($state == ProductStatusEnum::Reformed->value) {
                                                    return 'Quantité Réformée';
                                                } else {
                                                    return 'Quantité';
                                                }
                                            })
                                            ->placeholder('Quantité')
                                            ->hidden(function ($get, $record) {
                                                $state = $get('state');
                                                $productType = $record->product_type_id->value;

                                                // Eğer ürün türü "Other" değilse veya durum restored/reformed değilse alanı gizle
                                                if ($productType != ProductTypeEnum::Other->value) {
                                                    return true;
                                                }

                                                return ! in_array($state, [
                                                    ProductStatusEnum::Restored->value,
                                                    ProductStatusEnum::Reformed->value,
                                                ]);
                                            })
                                            ->options(function ($get, $record) {
                                                $state = $get('state');
                                                if ($state == null || $record->quantity == 0) {
                                                    return [];
                                                }

                                                return array_combine(range(1, ($record->quantity + $record->state_quantity)), range(1, ($record->quantity + $record->state_quantity)));
                                            })
                                            ->rules([
                                                'required_if:is_active,false',
                                            ])
                                            ->validationMessages([
                                                'required_if' => 'Vous devez entrer une quantité',
                                                'required' => 'Vous devez entrer une quantité',
                                            ])
                                            ->searchable()
                                            ->required(),
                                    ]),
                                Forms\Components\Textarea::make('state_description')
                                    ->hiddenLabel()
                                    ->placeholder('Veiullez décrire l\'état  actuel du produit tout en indiquant les raisons de la réforme ou de la remise')
                                    ->rules([
                                        'required_if:is_active,false',
                                    ])
                                    ->validationMessages([
                                        'required_if' => 'Vous devez décrire l\'état actuel du produit',
                                        'required' => 'Vous devez décrire l\'état actuel du produit',
                                    ])
                                    ->required()
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->mutateRelationshipDataBeforeCreateUsing(function ($data) {
                        $data['created_by'] = auth()->id();

                        return $data;
                    })
                    ->mutateRelationshipDataBeforeSaveUsing(function ($data) {
                        $data['updated_by'] = auth()->id();

                        return $data;
                    }),
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
                            ->relationship('product', 'name')
                            ->options(Product::query()
                                ->where('product_type_id', ProductTypeEnum::Electronic)
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
                            ->validationMessages([
                                'required' => 'Veuillez sélectionner un personnel',
                            ])
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('product_id')
                            ->label('Produit')
                            ->placeholder('Sélectionnez un produit')
                            ->relationship('product', 'name')
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
                            ->relationship('product', 'name')
                            ->options(Product::query()
                                ->where('product_type_id', ProductTypeEnum::Other)
                                ->where('quantity', '>', 0)
                                ->where('is_available', true)
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

    public function table(Table $table): Table
    {
        return $table
            // ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Nom du Produit'),
                Tables\Columns\TextColumn::make('product.product_type_id')
                    ->label('Type de Produit'),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantité'),
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Bénéficiaire'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Statut')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->visible(fn () => auth()->user()->hasRole('super_admin'))
                    ->label('Attribué par'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label("Date d'Attribution")
                    ->date('d/m/Y H:i'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->hidden(fn () => $this->isActiveEmployeeProduct())
                    ->mutateFormDataUsing(function ($data) {
                        $data['created_by'] = auth()->id();

                        $product = Product::find($data['product_id']);
                        $updatedBy = auth()->id();

                        if ($product->product_type_id->value != ProductTypeEnum::Other->value) { // Electronic or Vehicle
                            $product->update([
                                'is_available' => false,
                                'updated_by' => $updatedBy,
                            ]);
                        } else { // Other
                            $quantity = $data['quantity'];
                            $updatedQuantity = $product->quantity - $quantity;

                            if ($updatedQuantity < 0) {
                                $updatedQuantity = 0;
                            }

                            $product->update([
                                'quantity' => $updatedQuantity,
                                'is_available' => $updatedQuantity > 0,
                                'updated_by' => $updatedBy,
                            ]);
                        }

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->mutateFormDataUsing(function ($record, $data) {
                            $data['updated_by'] = auth()->id();
                            $userId = auth()->id();
                            $product = Product::find($record['product_id']);
                            $productTypeId = $product->product_type_id->value;

                            if (in_array($productTypeId, [ProductTypeEnum::Electronic->value, ProductTypeEnum::Vehicle->value])) {
                                if (! $data['is_active']) {
                                    $dataState = $data['state'];
                                    $dataStateDescription = $data['state_description'];

                                    if ($dataState == ProductStatusEnum::Restored->value) { // 1
                                        $product->update([
                                            'is_available' => $dataState,
                                            'updated_by' => $userId,
                                        ]);

                                        $record->update([
                                            'is_active' => ! $dataState,
                                            'state' => $dataState,
                                            'state_description' => $dataStateDescription,
                                            'updated_by' => $userId,
                                        ]);
                                    } elseif ($dataState == ProductStatusEnum::Reformed->value) { // 0
                                        $product->update([
                                            'is_available' => $dataState,
                                            'updated_by' => $userId,
                                        ]);

                                        $record->update([
                                            'is_active' => ! $dataState,
                                            'state' => $dataState,
                                            'state_description' => $dataStateDescription,
                                            'updated_by' => $userId,
                                        ]);
                                    }
                                } else {
                                    $product->update([
                                        'is_available' => false,
                                        'updated_by' => $userId,
                                    ]);

                                    $record->update([
                                        'is_active' => true,
                                        'updated_by' => $userId,
                                    ]);
                                }
                            } elseif ($productTypeId == ProductTypeEnum::Other->value) {
                                $productQuantity = $product->quantity;
                                $recordQuantity = $record->quantity;

                                if (! $data['is_active'] && $data['state'] == ProductStatusEnum::Restored->value) { // 1
                                    $recordStateQuantity = $record->state_quantity;
                                    $dataStateQuantity = $data['state_quantity'];
                                    $dataStateDescription = $data['state_description'];

                                    $updatedProductQuantity = $productQuantity; // Varsayılan olarak mevcut ürün miktarı
                                    $updatedRecordQuantity = $recordQuantity;  // Varsayılan olarak mevcut kayıt miktarı
                                    $updatedStateQuantity = $recordStateQuantity; // Varsayılan olarak mevcut state_quantity

                                    // Eğer recordStateQuantity null ise ilk durum
                                    if ($recordStateQuantity === null) {
                                        $updatedProductQuantity = $productQuantity + $dataStateQuantity;
                                        $updatedRecordQuantity = $recordQuantity - $dataStateQuantity;
                                        $updatedStateQuantity = $dataStateQuantity;
                                    } elseif ($recordStateQuantity < $dataStateQuantity) {
                                        // Eğer yeni state_quantity, mevcut state_quantity'den büyükse
                                        $diff = $dataStateQuantity - $recordStateQuantity;
                                        $updatedProductQuantity = $productQuantity + $diff;
                                        $updatedRecordQuantity = $recordQuantity - $diff;
                                        $updatedStateQuantity = $dataStateQuantity;
                                    } elseif ($recordStateQuantity > $dataStateQuantity) {
                                        // Eğer yeni state_quantity, mevcut state_quantity'den küçükse
                                        $diff = $recordStateQuantity - $dataStateQuantity;
                                        $updatedProductQuantity = $productQuantity - $diff;
                                        $updatedRecordQuantity = $recordQuantity + $diff;
                                        $updatedStateQuantity = $dataStateQuantity;
                                    }

                                    // Eğer miktarlar zaten eşitse, ürün miktarı değişmez
                                    if ($recordStateQuantity === $dataStateQuantity) {
                                        $updatedProductQuantity = $productQuantity;
                                    }

                                    // Ürün bilgilerini güncelle
                                    $product->update([
                                        'quantity' => $updatedProductQuantity,
                                        'is_available' => $updatedProductQuantity > 0,
                                        'updated_by' => $userId,
                                    ]);

                                    // Kayıt bilgilerini güncelle
                                    $record->update([
                                        'quantity' => $updatedRecordQuantity,
                                        'state_quantity' => $updatedStateQuantity,
                                        'state_description' => $dataStateDescription,
                                        'updated_by' => $userId,
                                        'is_active' => $updatedStateQuantity > 0,
                                    ]);

                                } else {
                                    $dataQuantity = $data['quantity'];

                                    if ($dataQuantity != $recordQuantity) {
                                        if ($dataQuantity > $recordQuantity) {
                                            $updatedQuantity = $productQuantity - ($dataQuantity - $recordQuantity);
                                        } else {
                                            $updatedQuantity = $productQuantity + ($recordQuantity - $dataQuantity);
                                        }
                                    } else {
                                        $updatedQuantity = $productQuantity;
                                    }

                                    // dd("active", $data, $record, $product);

                                    $product->update([
                                        'quantity' => $updatedQuantity,
                                        'is_available' => $updatedQuantity > 0,
                                        'updated_by' => $userId,
                                    ]);

                                    $record->update([
                                        'quantity' => $dataQuantity,
                                        'description' => $data['description'],
                                        'updated_by' => $userId,
                                    ]);
                                }
                            }

                            return $data;
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function isActiveEmployeeProduct(): bool
    {
        $employeeProductId = $this->ownerRecord->getKey();

        return ! $employeeProductId || \App\Models\EmployeeProduct::where('id', $employeeProductId)->where('status', 1)->exists();
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
