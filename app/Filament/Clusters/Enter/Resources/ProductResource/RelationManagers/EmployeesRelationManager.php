<?php

namespace App\Filament\Clusters\Enter\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EmployeesRelationManager extends RelationManager
{
    protected static string $relationship = 'employeeProductItems';

    protected static ?string $title = 'Bénéficiaires';

    /*protected static ?string $title = null;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return $ownerRecord->product_type_id->value == 3 ? 'Consommateurs' : 'Utilisateurs'; // product_type_id ProductTypeEnum'dan alınıyor.
    }*/

    protected static ?string $label = 'Bénéficiaires';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('employee.name')
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Nom'),
                Tables\Columns\TextColumn::make('employee.surname')
                    ->label('Prénoms'),
                Tables\Columns\TextColumn::make('employee.location.name')
                    ->label('Département'),
                Tables\Columns\TextColumn::make('employee.employee_type_id')
                    ->label('Type de Personnel'),
                Tables\Columns\TextColumn::make('employee.status')
                    ->label('Actuellement')
                    ->badge()
                    ->color(fn ($record) => match ($record->employee->status->value) {
                        1 => 'success',
                        2 => 'warning',
                        default => 'info',
                    }),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->visible(fn () => auth()->user()->hasRole('super_admin'))
                    ->label('Attribué par'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label("Date d'Attribution")
                    ->date('d/m/Y H:i'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                /*Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),*/
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
