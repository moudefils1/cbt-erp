<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EmployeeInternItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'internItems';

    public static function getLabel(): ?string
    {
        return 'Stagiaires Attribués';
    }

    public static function getPluralModelLabel(): ?string
    {
        return 'Stagiaires Attribués';
    }

    protected static ?string $icon = 'heroicon-o-square-3-stack-3d';

    protected static ?string $title = 'Stagiaires Attribués';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('intern.name')
                    ->label('Stagiaire'),
                Forms\Components\TextInput::make('intern.university')
                    ->label('Université'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('intern.name')
                    ->label('Stagiaire'),
                Tables\Columns\TextColumn::make('intern.university')
                    ->label('Université'),
                Tables\Columns\TextColumn::make('intern.department')
                    ->label('Filière'),
                Tables\Columns\TextColumn::make('intern.grade.name')
                    ->label('Niveau')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('intern.internship_type')
                    ->label('Type de stage')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Date de début')
                    ->badge()
                    ->color('primary')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Date de fin')
                    ->badge()
                    ->color(fn ($record) => $record->end_date < now()->format('Y-m-d') ? 'danger' : 'success')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Attribué par')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Attribué le')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updatedBy.name')
                    ->label('Modifié par')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->getStateUsing(fn ($record) => $record->updated_by ? $record->updated_at : null)
                    ->label('Modifié le')
                    ->date('d/m/Y')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                //
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

    protected function canDelete(Model $record): bool
    {
        return false;
    }

    protected function canEdit(Model $record): bool
    {
        return false;
    }
}
