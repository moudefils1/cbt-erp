<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeLeaveBalanceResource\Pages;
use App\Models\EmployeeLeaveBalance;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmployeeLeaveBalanceResource extends Resource
{
    protected static ?string $model = EmployeeLeaveBalance::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    public static function getLabel(): ?string
    {
        return 'Congés Acquis';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Congés Acquis';
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
            'index' => Pages\ListEmployeeLeaveBalances::route('/'),
            'create' => Pages\CreateEmployeeLeaveBalance::route('/create'),
            'edit' => Pages\EditEmployeeLeaveBalance::route('/{record}/edit'),
        ];
    }
}
