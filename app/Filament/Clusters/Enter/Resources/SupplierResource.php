<?php

namespace App\Filament\Clusters\Enter\Resources;

use App\Filament\Clusters\Enter;
use App\Filament\Resources\SupplierResource\RelationManagers;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    public static function getLabel(): ?string
    {
        return 'Fournisseur';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Fournisseurs';
    }

    //    public static function getNavigationBadge(): ?string
    //    {
    //        if (auth()->user()->hasRole('super_admin') || auth()->user()->can('view_all_supplier')) {
    //            return static::getModel()::count();
    //        } else {
    //            return static::getModel()::where('created_by', auth()->id())->count();
    //        }
    //    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where(function ($query) {

            if (auth()->user()?->hasRole('super_admin') || auth()->user()?->can('view_all_supplier')
            ) {
                return $query;
            }

            return $query->where('created_by', auth()->id());
        });
    }

    // protected static ?string $navigationGroup = "Gestion des Matériels";

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $cluster = Enter::class;

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du Fournisseur')
                    ->description('Veuillez renseigner les informations du fournisseur')
                    ->schema([
                        Forms\Components\TextInput::make('enterprise_name')
                            ->label('Nom de l\'entreprise')
                            ->placeholder('Nom de l\'entreprise')
                            ->required(),
                        Forms\Components\TextInput::make('nif')
                            ->label('NIF')
                            ->placeholder('Numéro d\'Identification Fiscale')
                            ->unique(
                                'suppliers',
                                'nif',
                                ignoreRecord: true,
                                modifyRuleUsing: function ($rule) {
                                    return $rule->whereNull('deleted_at');
                                }
                            )
                            ->validationMessages([
                                'nni.unique' => 'Ce NIF existe déjà',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('full_name')
                            ->label('Nom complet du fournisseur')
                            ->placeholder('Nom complet du fournisseur')
                            ->required(),
                        Forms\Components\TextInput::make('phone')
                            ->label('Téléphone')
                            ->placeholder('Téléphone du fournisseur')
                            ->minLength(8)
                            ->maxLength(8)
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
                                'phone.unique' => 'Ce numéro de téléphone existe déjà',
                            ])
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Statut')
                            ->helperText('Actif ou Inactif')
                            ->onColor('success')
                            ->offColor('danger')
                            ->visibleOn('edit')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('enterprise_name')
                    ->label('Nom de l\'Entreprise'),
                Tables\Columns\TextColumn::make('nif')
                    ->label('NIF')
                    ->searchable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nom du Fournisseur')
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoices_count')
                    ->label('Factures')
                    ->counts('invoices')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Statut')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->visible(fn ($record) => auth()->user()->hasRole('super_admin'))
                    ->label('Créé par')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->date('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->hidden(fn ($record) => $record->trashed()),
                    Tables\Actions\EditAction::make()
                        ->hidden(fn ($record) => $record->trashed()),
                    Tables\Actions\DeleteAction::make()
                        ->hidden(fn ($record) => $record->invoices()->exists() || $record->trashed()),
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
            // RelationManagers\ElectronicProductsRelationManager::class,
            // Enter\Resources\SupplierResource\RelationManagers\SuppliedProductsRelationManager::class,
            Enter\Resources\SupplierResource\RelationManagers\InvoicesRelationManager::class,

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Enter\Resources\SupplierResource\Pages\ListSuppliers::route('/'),
            // 'create' => Enter\Resources\SupplierResource\Pages\CreateSupplier::route('/create'),
            'edit' => Enter\Resources\SupplierResource\Pages\EditSupplier::route('/{record}/edit'),
            'view' => Enter\Resources\SupplierResource\Pages\ViewSupplier::route('/{record}'),
        ];
    }

    // Infolist for the supplier
    public static function infoList(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Card::make()
                    ->schema([
                        Infolists\Components\Section::make('Informations du Fournisseur')
                            ->collapsible()
                            ->schema([
                                Infolists\Components\TextEntry::make('enterprise_name')
                                    ->label('Nom de l\'entreprise'),
                                Infolists\Components\TextEntry::make('nif')
                                    ->label('NIF'),
                                Infolists\Components\TextEntry::make('full_name')
                                    ->label('Fournisseur'),
                                Infolists\Components\TextEntry::make('phone')
                                    ->label('Téléphone'),
                                Infolists\Components\TextEntry::make('createdBy.name')
                                    ->label('Créé par')
                                    ->badge()
                                    ->color('info'),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Date de Création')
                                    ->date('d/m/Y H:i')
                                    ->badge()
                                    ->color('info'),
                                Infolists\Components\TextEntry::make('updatedBy.name')
                                    ->label('Modifié par')
                                    ->badge()
                                    ->color('success')
                                    ->visible(fn ($record) => $record->updated_by != null),
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Dernière Modification')
                                    ->date('d/m/Y H:i')
                                    ->badge()
                                    ->color('success')
                                    ->visible(fn ($record) => $record->updated_by != null),
                                Infolists\Components\IconEntry::make('is_active')
                                    ->label('Statut')
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle'),
                            ])
                            ->columns(4),
                    ]),
            ]);
    }
}
