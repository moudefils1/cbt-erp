<?php

namespace App\Filament\Resources;

use App\Enums\SalaryDeductionTypeEnum;
use App\Filament\Resources\SalaryDeductionResource\Pages;
use App\Models\SalaryDeduction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SalaryDeductionResource extends Resource
{
    protected static ?string $model = SalaryDeduction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Gestion du Paiement';

    public static function shouldRegisterNavigation(): bool
    {
        return config('module.salary_deductions.enable', true);
    }

    public static function getLabel(): ?string
    {
        return 'Typé de Prélèvement';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Types de Prélèvements';
    }

    public static function getNavigationBadge(): ?string
    {
        if (auth()->user()->hasRole('super_admin') || auth()->user()->can('view_all_salary_deductions')) {
            return static::getModel()::count();
        } else {
            return static::getModel()::where('created_by', auth()->id())->count();
        }
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where(function ($query) {

            if (auth()->user()?->hasRole('super_admin') || auth()->user()?->can('view_all_salary_deductions')
            ) {
                return $query;
            }

            return $query->where('created_by', auth()->id());
        });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Fieldset::make('Informations')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nom')
                                    ->placeholder('Nom du prélevé de salaire')
                                    ->validationMessages([
                                        'required' => 'Le nom du prélevé de salaire est requis.',
                                    ])
                                    ->required(),
                                Forms\Components\Select::make('type')
                                    ->label('Type de prélèvement')
                                    ->placeholder('Sélectionner un type')
                                    ->options(SalaryDeductionTypeEnum::class)
                                    ->validationMessages([
                                        'required' => 'Le type du prélevé de salaire est requis.',
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('value')
                                    ->label('Valeur')
                                    ->placeholder('Valeur du prélevé de salaire')
                                    ->numeric()
                                    ->minValue(0)
                                    ->validationMessages([
                                        'required' => 'Le montant du prélevé de salaire est requis.',
                                        'min_value' => 'Le montant doit être supérieur ou égal à 0.',
                                        'numeric' => 'Le montant doit être un nombre.',
                                    ])
                                    ->required(),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type de prélèvement')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Valeur')
                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->visible(fn () => auth()->user()->hasRole('super_admin'))
                    ->label('Créé par')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->date('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updatedBy.name')
                    ->label('Modifié par')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->getStateUsing(fn ($record) => $record->updated_by ? $record->updated_at : null)
                    ->label('Modifié le')
                    ->date('d/m/Y H:i')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->modalHeading('Détails du prélèvement de salaire')
                        ->mutateFormDataUsing(function (array $data) {
                            $data['updated_by'] = auth()->id();

                            return $data;
                        }),
                    Tables\Actions\EditAction::make()
                        ->modalHeading('Modifier le prélèvement de salaire')
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalaryDeductions::route('/'),
            // 'create' => Pages\CreateSalaryDeduction::route('/create'),
            // 'edit' => Pages\EditSalaryDeduction::route('/{record}/edit'),
            // 'view' => Pages\ViewSalaryDeduction::route('/{record}'),
        ];
    }
}
