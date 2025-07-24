<?php

namespace App\Filament\Clusters\Sortie\Resources;

use App\Filament\Clusters\Sortie;
use App\Filament\Clusters\Sortie\Resources\EmployeeProductItemResource\Pages;
use App\Models\EmployeeProductItem;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmployeeProductItemResource extends Resource
{
    protected static ?string $model = EmployeeProductItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Sortie::class;

    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = false;

    public static function getLabel(): ?string
    {
        return 'Produit Sortie';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Produits Sortie';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeeProductItems::route('/'),
            'create' => Pages\CreateEmployeeProductItem::route('/create'),
            'edit' => Pages\EditEmployeeProductItem::route('/{record}/edit'),
        ];
    }
}
