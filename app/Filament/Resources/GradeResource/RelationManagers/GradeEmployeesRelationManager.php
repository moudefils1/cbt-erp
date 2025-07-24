<?php

namespace App\Filament\Resources\GradeResource\RelationManagers;

use App\Filament\Resources\EmployeeResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class GradeEmployeesRelationManager extends RelationManager
{
    protected static string $relationship = 'employees';

    public static function getLabel(): ?string
    {
        return 'Personnels';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Personnels';
    }

    protected static ?string $icon = 'heroicon-o-user-group';

    protected static ?string $title = 'Personnels';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nom complet'),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Département'),
                Tables\Columns\TextColumn::make('employee_type_id')
                    ->label('Type de personnel'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Actuellement')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->url(fn ($record) => EmployeeResource::getUrl('view', ['record' => $record])),
                    Tables\Actions\EditAction::make()
                        ->url(fn ($record) => EmployeeResource::getUrl('edit', ['record' => $record])),
                ]),
            ])
            ->bulkActions([
                //
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    protected function canCreate(): bool
    {
        return false;
    }

    protected function canDelete($record): bool
    {
        return false;
    }
}
