<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeePositionResource\Pages;
use App\Models\EmployeePosition;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmployeePositionResource extends Resource
{
    protected static ?string $model = EmployeePosition::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getLabel(): ?string
    {
        return 'Poste Occupé';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Postes Occupés';
    }

    protected static bool $shouldRegisterNavigation = false;

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
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListEmployeePositions::route('/'),
            'create' => Pages\CreateEmployeePosition::route('/create'),
            'edit' => Pages\EditEmployeePosition::route('/{record}/edit'),
        ];
    }
}
