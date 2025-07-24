<?php

namespace App\Filament\Clusters\Enter\Resources\ProductResource\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestProduct extends BaseWidget
{
    protected static ?string $heading = 'Fournitures';

    public function table(Table $table): Table
    {
        return $table
            ->description('Liste des 10 dernières fournitures ajoutées.')
            ->paginated(false)
            ->query(
                Product::query()
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->label('Nom'),
                Tables\Columns\TextColumn::make('product_type_id')
                    ->sortable()
                    ->label('Type de Produit'),
                Tables\Columns\IconColumn::make('is_available')
                    ->boolean()
                    ->label('Disponibilité'),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->visible(fn () => auth()->user()->hasRole('super_admin'))
                    ->label('Créé par')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Crée le')
                    ->sortable()
                    ->date('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('updatedBy.name')
                    ->label('Modifié par')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->getStateUsing(fn ($record) => $record->updated_by ? $record->updated_at : null)
                    ->label('Modifié le')
                    ->sortable()
                    ->date('d/m/Y H:i'),
            ]);
    }

    public function getColumnSpan(): int|string
    {
        return 'full';
    }

    public static function canView(): bool
    {
        return auth()->user()->can('widget_LatestProduct');
    }
}
