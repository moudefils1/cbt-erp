<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InternItemResource\Pages;
use App\Models\InternItem;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InternItemResource extends Resource
{
    protected static ?string $model = InternItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Gestion des Stagiaires';

    protected static ?int $navigationSort = 3;

    protected static bool $shouldRegisterNavigation = false; // Disable the navigation for this resource

    public static function getLabel(): ?string
    {
        return 'Départements de Stage';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Départements de Stage';
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
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
                Tables\Columns\TextColumn::make('intern.name')
                    ->label('Stagiaire'),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Département'),
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Employé'),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Date de Début')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Date de Fin')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
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
            'index' => Pages\ListInternItems::route('/'),
            'create' => Pages\CreateInternItem::route('/create'),
            'edit' => Pages\EditInternItem::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
