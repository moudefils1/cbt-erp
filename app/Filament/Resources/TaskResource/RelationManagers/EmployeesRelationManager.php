<?php

namespace App\Filament\Resources\TaskResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EmployeesRelationManager extends RelationManager
{
    protected static string $relationship = 'employees';

    protected static ?string $title = 'Personnels Liés au Poste';

    protected static ?string $label = 'Personnel';

    protected static ?string $pluralLabel = 'Personnels';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom du Personnel')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('surname')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee_type_id')
                    ->label('Type de Personnel')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Actuellement')
                    ->badge()
                    ->color(fn ($record) => match ($record->status->value) {
                        1 => 'success',
                        2 => 'warning',
                        3 => 'info',
                        default => 'danger',
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->url(fn ($record) => \App\Filament\Resources\EmployeeResource::getUrl('view', ['record' => $record])),
                    Tables\Actions\EditAction::make()
                        ->url(fn ($record) => \App\Filament\Resources\EmployeeResource::getUrl('edit', ['record' => $record])),
                    Tables\Actions\DeleteAction::make()
                        // hidden if employee product exists
                        ->hidden(fn ($record) => $record->employeeProductItems()->exists()),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
