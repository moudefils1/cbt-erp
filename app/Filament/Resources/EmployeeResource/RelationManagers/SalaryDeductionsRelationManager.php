<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SalaryDeductionsRelationManager extends RelationManager
{
    protected static string $relationship = 'salaryDeductions';

    protected static ?string $title = 'Prélevés de Salaire';

    protected static ?string $label = 'Prélevé de Salaire';

    protected static ?string $pluralLabel = 'Prélevés de Salaire';

    protected static ?string $icon = 'heroicon-o-banknotes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading('Ajouter une dédiction de salaire')
                    ->mutateFormDataUsing(function (array $data) {
                        $data['created_by'] = auth()->id();

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->modalHeading('Modifier la dédiction de salaire')
                        ->mutateFormDataUsing(function (array $data) {
                            $data['updated_by'] = auth()->id();

                            return $data;
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->action(function ($record) {
                            $record->update(['deleted_by' => auth()->id()]);
                            $record->delete();
                        }),
                ]),
            ])
            ->bulkActions([
                //
            ]);
    }
}
