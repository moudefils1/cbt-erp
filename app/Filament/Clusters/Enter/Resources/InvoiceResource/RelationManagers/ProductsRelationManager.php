<?php

namespace App\Filament\Clusters\Enter\Resources\InvoiceResource\RelationManagers;

use App\Enums\ProductTypeEnum;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $label = 'Produits Fournis';
    protected static ?string $title = 'Produits Fournis';
    protected static ?string $icon = 'heroicon-o-list-bullet';

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
                            ->schema([
                                self::getElectronicFields(),
                            ]),
                        Forms\Components\Grid::make()
                            ->visible(fn ($get) => $get('product_type_id') == ProductTypeEnum::Vehicle->value)
                            ->schema([
                                self::getVehicleFields(),
                            ]),
                        Forms\Components\Grid::make()
                            ->visible(fn ($get) => $get('product_type_id') == ProductTypeEnum::Other->value)
                            ->schema([
                                self::getOtherFields(),
                            ]),
                    ]),
            ]);
    }

    /*private static function getProductSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make("Informations des Produits Fournis")
            ->description("Veuillez renseigner les informations des produits fournis.")
            ->collapsible()
            //->hiddenOn(['edit'])
            ->columnSpanFull()
            ->schema([
                Forms\Components\Repeater::make('products')
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
                    ->mutateRelationshipDataBeforeSaveUsing(function ($data) {
                        $data['updated_by'] = auth()->id();
                        return $data;
                    })
                    ->columns(2)
                    ->collapsible(),
            ]);
    }*/

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
                    ->visibleOn('view')
                    ->helperText('Le produit est-il disponible ?')
                    ->onColor('success')
                    ->offColor('warning')
                    ->default(true),
            ])
            /*->mutateRelationshipDataBeforeCreateUsing(function ($data) {
                $data['created_by'] = auth()->id();
                $data['product_type_id'] = $data['type'];
                return $data;
            })
            ->mutateRelationshipDataBeforeSaveUsing(function ($data) {
                $data['updated_by'] = auth()->id();
                return $data;
            })*/;
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
                Forms\Components\TextInput::make('chassis_number')
                    ->label('Numéro de Chassis')
                    ->placeholder('Numéro de Chassis du véhicule')
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => 'Ce numéro de chassis existe déjà',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('plate_number')
                    ->label('Numéro de Plaque')
                    ->placeholder('Numéro de Plaque du véhicule')
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => 'Ce numéro de plaque existe déjà',
                    ]),
                Forms\Components\TextInput::make('description')
                    ->label('Description')
                    ->placeholder('Description du véhicule')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_available')
                    ->label('Disponibilité')
                    ->visibleOn('view')
                    ->helperText('Le produit est-il disponible ?')
                    ->onColor('success')
                    ->offColor('warning')
                    ->default(true),
            ])
            /*->mutateRelationshipDataBeforeCreateUsing(function ($data) {
                $data['created_by'] = auth()->id();
                $data['product_type_id'] = $data['type'];
                return $data;
            })
            ->mutateRelationshipDataBeforeSaveUsing(function ($data) {
                $data['updated_by'] = auth()->id();
                return $data;
            })*/;
    }

    private static function getOtherFields(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Vivres et Autres Produits Fournis')
            ->columns(2)
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->placeholder('Nom du produit')
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->label('Quantité')
                    ->placeholder('Quantité du produit')
                    ->numeric()
                    ->validationMessages([
                        'integer' => 'La quantité doit être un nombre entier',
                        'regex' => 'La quantité ne doit pas contenir de point ou de virgule',
                    ])
                    ->readOnly(fn ($record) => $record && $record->employeeProductItems()?->exists())
                    ->required(),
                Forms\Components\TextInput::make('description')
                    ->label('Description')
                    ->placeholder('Description du produit')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_available')
                    ->label('Disponibilité')
                    ->hiddenOn(['create'])
                    ->helperText('Le produit est-il disponible ?')
                    ->onColor('success')
                    ->offColor('warning')
                    ->default(true),
            ])
            /*->mutateRelationshipDataBeforeCreateUsing(function ($data) {
                $data['created_by'] = auth()->id();
                $data['product_type_id'] = $data['type'];
                return $data;
            })
            ->mutateRelationshipDataBeforeSaveUsing(function ($data) {
                $data['updated_by'] = auth()->id();
                return $data;
            })*/;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product_type_id')
                    ->label('Type de Produit')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantité')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_available')
                    ->boolean()
                    ->label('Disponibilité')
                    ->trueColor('success')
                    ->falseColor('danger'),
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
            ->heading('Produits Fournis')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->hidden(fn () => $this->shouldHideAction())
                    ->mutateFormDataUsing(function ($data) {
                        $data['created_by'] = auth()->id();

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->hidden(fn ($record) => $this->shouldHideAction())
                        ->mutateRecordDataUsing(function ($data) {
                            $data['updated_by'] = auth()->id();

                            return $data;
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->hidden(fn ($record) => $record->employeeProductItems()->exists() || $this->shouldHideAction()),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // utilisé pour afficher ou cacher les buttons d'actions
    protected function shouldHideAction(): bool
    {
        $invoiceId = $this->ownerRecord->getKey();

        // on ne peut ajouter des produits à une facture annulée ou supprimée
        return ! $invoiceId || \App\Models\Invoice::where('id', $invoiceId)->where('invoice_status', 1)->exists();
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
