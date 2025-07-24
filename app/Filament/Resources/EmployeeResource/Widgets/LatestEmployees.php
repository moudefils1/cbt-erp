<?php

namespace App\Filament\Resources\EmployeeResource\Widgets;

use App\Models\Employee;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestEmployees extends BaseWidget
{
    protected static ?string $heading = 'Personnels';

    public function table(Table $table): Table
    {
        return $table
            ->description('Liste des 10 derniers personnels ajoutés.')
            ->paginated(false)
            ->query(
                Employee::query()
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->label('Nom'),
                Tables\Columns\TextColumn::make('surname')
                    ->sortable()
                    ->label('Prénom'),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Département')
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee_type_id')
                    ->label('Type de Personnel')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Actuellement')
                    ->badge(),
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
        return auth()->user()->can('widget_LatestEmployees');
    }
}
