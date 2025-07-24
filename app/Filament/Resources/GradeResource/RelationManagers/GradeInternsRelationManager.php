<?php

namespace App\Filament\Resources\GradeResource\RelationManagers;

use App\Enums\StateEnum;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class GradeInternsRelationManager extends RelationManager
{
    protected static string $relationship = 'interns';

    protected static ?string $title = 'Stagiaires';

    protected static ?string $label = 'Stagiaire';

    protected static ?string $pluralLabel = 'Stagiaires';

    protected static ?string $icon = 'heroicon-o-square-3-stack-3d';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom et Prénom'),
                Tables\Columns\TextColumn::make('university')
                    ->label('Université'),
                Tables\Columns\TextColumn::make('department')
                    ->label('Filière'),
                Tables\Columns\TextColumn::make('internship_start_date')
                    ->label('Date de Début')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('internship_end_date')
                    ->label('Date de Fin')
                    ->date('d/m/Y')
                    ->badge()
                    ->color(fn ($record) => $record->internship_end_date->isPast() ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data) {
                        $data['created_by'] = auth()->id();
                        $data['internship_duration'] = date_diff(date_create($data['internship_start_date']), date_create($data['internship_end_date']))->format('%a');

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->url(fn ($record) => \App\Filament\Resources\InternResource::getUrl('view', ['record' => $record])),
                    Tables\Actions\EditAction::make()
                        ->url(fn ($record) => \App\Filament\Resources\InternResource::getUrl('edit', ['record' => $record])),
                    Tables\Actions\DeleteAction::make()
                        ->hidden(fn ($record) => $record->trashed() || $record->status->value == StateEnum::COMPLETED->value),
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

    public function canCreate(): bool
    {
        return false;
    }

    public function canDelete($record): bool
    {
        return false;
    }
}
