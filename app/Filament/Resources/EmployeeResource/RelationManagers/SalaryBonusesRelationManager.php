<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use App\Enums\StatusEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Components\Tab;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SalaryBonusesRelationManager extends RelationManager
{
    protected static string $relationship = 'salaryBonuses';

    protected static ?string $title = 'Primes';

    protected static ?string $label = 'Prime';

    protected static ?string $pluralLabel = 'Primes';

    protected static ?string $icon = 'heroicon-o-banknotes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('Détails de la Prime')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->placeholder('Nom de la prime')
                            ->validationMessages([
                                'required' => 'Le nom de la prime est obligatoire.',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Montant')
                            ->placeholder('Montant de la prime')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        Forms\Components\Fieldset::make('Description')
                            ->schema([
                                Forms\Components\Textarea::make('description')
                                    ->hiddenLabel()
                                    ->placeholder('Description de la prime')
                                    ->columnSpanFull(),
                            ])->columns(1),
                    ])->columns(2),
                Forms\Components\Fieldset::make('Statut')
                    ->hiddenOn('create')
                    ->schema([
                        Forms\Components\Toggle::make('status')
                            ->label('Statut')
                            ->helperText('Rouge = Inactif, Vert = Actif')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->required(),
                    ])->columns(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom de la Prime')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Montant')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, ',', ' ') . ' CFA')
                    ->sortable(
                        query: function ($query, $direction) {
                            $query->orderBy('amount', $direction);
                        }
                    )
                    ->badge()
                    ->colors([
                        'success' => fn ($record) => $record->status === StatusEnum::ACTIVE,
                        'danger' => fn ($record) => $record->status === StatusEnum::INACTIVE,
                    ]),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
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
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading('Ajouter une Prime')
                    ->mutateFormDataUsing(function (array $data) {
                        $data['created_by'] = auth()->id();

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->modalHeading('Modifier la Prime')
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

    public function isReadOnly(): bool
    {
        return false;
    }

    public function getTabs(): array
    {
        $tabs = [];

        $tabs['active_amount'] = Tab::make('Total Actif')
            ->badge(fn () => number_format($this->ownerRecord->salaryBonuses?->where('status', StatusEnum::ACTIVE)->sum('amount'), 2) . ' CFA')
            ->badgeIcon('heroicon-o-banknotes')
            ->badgeColor('success');

        $tabs['inactive_amount'] = Tab::make('Total Passif')
            ->badge(fn () => number_format($this->ownerRecord->salaryBonuses?->where('status', StatusEnum::INACTIVE)->sum('amount'), 2) . ' CFA')
            ->badgeIcon('heroicon-o-banknotes')
            ->badgeColor('danger');

        return $tabs;
    }
}
