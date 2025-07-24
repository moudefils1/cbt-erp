<?php

namespace App\Filament\Resources\DashboardResource\Widgets;

use App\Models\Vehicle;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestVehicules extends BaseWidget
{
    protected static ?string $heading = 'Liste des 10 derniers véhicules ajoutés';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Vehicle::query()
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->label('Nom'),
                Tables\Columns\TextColumn::make('brand')
                    ->sortable()
                    ->label('Marque'),
                Tables\Columns\TextColumn::make('model')
                    ->sortable()
                    ->label('Modèle'),
            ]);
    }
}
