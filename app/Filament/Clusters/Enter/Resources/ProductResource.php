<?php

namespace App\Filament\Clusters\Enter\Resources;

use App\Enums\ProductTypeEnum;
use App\Filament\Clusters\Enter;
use App\Filament\Clusters\Enter\Resources\ProductResource\Pages;
use App\Filament\Clusters\Enter\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $cluster = Enter::class;

    protected static ?int $navigationSort = 3;

    public static function getLabel(): ?string
    {
        return 'Produit';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Produits';
    }

    //    public static function getNavigationBadge(): ?string
    //    {
    //        if (auth()->user()->hasRole('super_admin') || auth()->user()->can('view_all_product')) {
    //            return static::getModel()::count();
    //        } else {
    //            return static::getModel()::where('created_by', auth()->id())->count();
    //        }
    //    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where(function ($query) {

            if (auth()->user()?->hasRole('super_admin') || auth()->user()?->can('view_all_product')
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
                    ->visible(fn ($record) => $record->product_type_id->value == ProductTypeEnum::Electronic->value)
                    ->schema([
                        self::getElectronicFields(),
                    ]),

                Forms\Components\Grid::make()
                    ->visible(fn ($record) => $record->product_type_id->value == ProductTypeEnum::Vehicle->value)
                    ->schema([
                        self::getVehicleFields(),
                    ]),

                Forms\Components\Grid::make()
                    ->visible(fn ($record) => $record->product_type_id->value == ProductTypeEnum::Other->value)
                    ->schema([
                        self::getOtherFields(),
                    ]),
            ]);
    }

    private static function getElectronicFields(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Matériels Électroniques Fournis')
            ->columns(2)
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
                Forms\Components\Fieldset::make('Description')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->hiddenLabel()
                            ->placeholder('Description du matériel')
                            ->columnSpanFull(),
                    ]),
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
            ->columns(2)
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
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('chassis_number')
                    ->label('Numéro de Chassis')
                    ->placeholder('Numéro de Chassis du véhicule')
                    ->unique(ignoreRecord: true)
                    ->required(),
                Forms\Components\Fieldset::make('Description')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->hiddenLabel()
                            ->placeholder('Description du véhicule')
                            ->columnSpanFull(),
                    ]),
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
                    ->required(),
                Forms\Components\Fieldset::make('Description')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->hiddenLabel()
                            ->placeholder('Description du produit')
                            ->columnSpanFull(),
                    ]),
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
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom du Produit')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product_type_id')
                    ->label('Type de Produit')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_available')
                    ->boolean()
                    ->label('Disponibilité')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantité')
                    ->sortable(),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->visible(fn () => auth()->user()->hasRole('super_admin'))
                    ->label('Créé par')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->date('d/m/Y')
                    ->alignEnd()
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                // filter by product type
                Tables\Filters\SelectFilter::make('product_type_id')
                    ->label('Type de Produit')
                    ->options(ProductTypeEnum::class)
                    ->searchable()
                    ->default(null),
                // filter by availability
                Tables\Filters\SelectFilter::make('is_available')
                    ->label('Disponibilité')
                    ->options([
                        '1' => 'Disponible',
                        '0' => 'Attribué',
                    ])
                    ->default(null),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->hidden(fn ($record) => $record->trashed()),
                    Tables\Actions\EditAction::make()
                        ->hidden(fn ($record) => $record->trashed()),
                    Tables\Actions\DeleteAction::make()
                        ->hidden(fn ($record) => $record->employeeProductItems()->exists() || $record->trashed()),
                    Tables\Actions\RestoreAction::make()
                        ->hidden(fn ($record) => ! $record->trashed()),
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
            'employeeProductItems' => RelationManagers\EmployeesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
            'view' => Pages\ViewProduct::route('/{record}'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Card::make()
                    ->schema([
                        Infolists\Components\Section::make('Informations du Produit')
                            ->schema([
                                Infolists\Components\TextEntry::make('invoice.invoice_number')
                                    ->label('Facture N°'),
                                Infolists\Components\TextEntry::make('product_type_id')
                                    ->label('Type de Produit')
                                    ->visible(fn ($record) => $record->product_type_id != null),
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Nom du Produit'),
                                Infolists\Components\TextEntry::make('brand')
                                    ->label('Marque')
                                    ->visible(fn ($record) => $record->brand != null),
                                Infolists\Components\TextEntry::make('model')
                                    ->label('Modèle')
                                    ->visible(fn ($record) => $record->model != null),
                                Infolists\Components\TextEntry::make('serial_number')
                                    ->label('Numéro de Série')
                                    ->visible(fn ($record) => $record->serial_number != null),
                                Infolists\Components\TextEntry::make('mac_address')
                                    ->label('Adresse MAC')
                                    ->visible(fn ($record) => $record->mac_address != null),
                                Infolists\Components\TextEntry::make('chassis_number')
                                    ->label('Numéro de Châssis')
                                    ->visible(fn ($record) => $record->chassis_number != null),
                                Infolists\Components\TextEntry::make('quantity')
                                    ->label('Quantité')
                                    ->visible(fn ($record) => $record->quantity != null),
                                Infolists\Components\TextEntry::make('createdBy.full_name')
                                    ->label('Créé par')
                                    ->badge()
                                    ->color('info')
                                    ->visible(fn ($record) => $record->created_by != null),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Créé le')
                                    ->date('d/m/Y H:i:s')
                                    ->badge()
                                    ->color('info')
                                    ->visible(fn ($record) => $record->created_at != null),
                                Infolists\Components\TextEntry::make('updatedBy.full_name')
                                    ->label('Déernière Modification')
                                    ->badge()
                                    ->color('success')
                                    ->visible(fn ($record) => $record->updated_by != null),
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Modifié le')
                                    ->date('d/m/Y H:i:s')
                                    ->badge()
                                    ->color('success')
                                    ->visible(fn ($record) => $record->updated_by != null),
                                Infolists\Components\IconEntry::make('is_available')
                                    ->label('Disponible')
                                    ->trueColor('success')
                                    ->falseColor('danger'),
                                Infolists\Components\TextEntry::make('description')
                                    ->label('Description')
                                    ->visible(fn ($record) => $record->description != null),
                            ])->columns(4),
                        Infolists\Components\Section::make('Informations du Fourisseur')
                            ->schema([
                                Infolists\Components\TextEntry::make('invoice.supplier.enterprise_name')
                                    ->label('Nom de l\'Entreprise')
                                    ->visible(fn ($record) => $record->invoice->supplier->enterprise_name != null),
                                Infolists\Components\TextEntry::make('invoice.supplier.full_name')
                                    ->label('Nom du Fournisseur')
                                    ->visible(fn ($record) => $record->invoice->supplier->full_name != null),
                                Infolists\Components\TextEntry::make('invoice.supplier.phone')
                                    ->label('Téléphone')
                                    ->visible(fn ($record) => $record->invoice->supplier->phone != null),
                                Infolists\Components\TextEntry::make('invoice.supplier.createdBy.full_name')
                                    ->label('Créé par')
                                    ->badge()
                                    ->color('info')
                                    ->visible(fn ($record) => $record->invoice->supplier->created_by != null),
                                Infolists\Components\TextEntry::make('invoice.supplier.created_at')
                                    ->label('Créé le')
                                    ->date('d/m/Y H:i:s')
                                    ->badge()
                                    ->color('info')
                                    ->visible(fn ($record) => $record->invoice->supplier->created_at != null),
                                Infolists\Components\TextEntry::make('invoice.supplier.updatedBy.full_name')
                                    ->label('Déernière Modification')
                                    ->badge()
                                    ->color('success')
                                    ->visible(fn ($record) => $record->invoice->supplier->updated_by != null),
                                Infolists\Components\TextEntry::make('invoice.supplier.updated_at')
                                    ->label('Modifié le')
                                    ->date('d/m/Y H:i:s')
                                    ->badge()
                                    ->color('success')
                                    ->visible(fn ($record) => $record->invoice->supplier->updated_by != null),
                                Infolists\Components\IconEntry::make('invoice.supplier.is_active')
                                    ->label('Statut')
                                    ->trueColor('success')
                                    ->falseColor('danger'),
                            ])->columns(4),
                    ]),
            ]);
    }
}
