<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use App\Filament\Resources\DebtResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Components\Tab;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DebtsRelationManager extends RelationManager
{
    protected static string $relationship = 'debts';

    protected static ?string $title = 'Prêts';

    protected static ?string $icon = 'heroicon-o-banknotes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Prêt')
                            ->placeholder('Nom du prêt')
                            ->validationMessages([
                                'required' => 'Le nom du prêt est obligatoire.',
                            ])
                            ->columnSpanFull()
                            ->required(),
                        Forms\Components\Fieldset::make('Détails du prêt')
                            ->schema([
                                Forms\Components\TextInput::make('amount')
                                    ->label('Montant')
                                    ->placeholder('Montant emprunté')
                                    ->numeric()
                                    ->minValue(0)
                                    ->validationMessages([
                                        'required' => 'Le montant est obligatoire.',
                                        'numeric' => 'Le montant doit être un nombre.',
                                        'min' => 'Le montant doit être un nombre positif.',
                                    ])
                                    // ->readOnly(fn ($record) => $record && $record->items()->exists())
                                    ->required(),
                                Forms\Components\DatePicker::make('borrowed_at')
                                    ->label('Date d\'Emprunt')
                                    ->placeholder('Date d\'emprunt')
                                    ->validationMessages([
                                        'required' => 'La date d\'emprunt est obligatoire.',
                                    ])
                                    // ->readOnly(fn ($record) => $record && $record->items()->exists())
                                    ->default(now())
                                    ->minDate($this->ownerRecord->hiring_date)?->format('Y-m-d')
                                    ->required(),
                                Forms\Components\Textarea::make('reason')
                                    ->label('Raison')
                                    ->placeholder('Raison de l\'emprunt (facultatif)')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Prêt')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Montant')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, ',', ' ') . ' CFA')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('borrowed_at')
                    ->label('Date d\'Emprunt')
                    ->searchable()
                    ->date('d/m/Y')
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
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading('Ajouter un Prêt')
                    ->mutateFormDataUsing(function (array $data) {
                        $data['created_by'] = auth()->id();

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->url(fn ($record) => DebtResource::getUrl('view', ['record' => $record])),
                    Tables\Actions\EditAction::make()
                        ->modalHeading('Modifier un Prêt')
                        ->mutateFormDataUsing(function (array $data, $record) {
                            $data['updated_by'] = auth()->id();

                            return $data;
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->modalHeading('Supprimer un Prêt'),
                    // ->hidden(fn ($record) => $record->items()->exists() || $record->trashed()),
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

        $tabs['debts'] = Tab::make('Emprunts Total')
            ->badge(fn () => number_format($this->ownerRecord->debts->sum('amount'), 2) . ' CFA')
            ->badgeIcon('heroicon-o-banknotes')
            ->badgeColor('primary');

        return $tabs;
    }
}
