<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DebtResource\Pages;
use App\Filament\Resources\DebtResource\RelationManagers;
use App\Models\Debt;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DebtResource extends Resource
{
    protected static ?string $model = Debt::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Gestion du Paiement';

    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = false;

    public static function getLabel(): ?string
    {
        return 'Prêt';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Prêts';
    }

    public static function getNavigationBadge(): ?string
    {
        if (auth()->user()->hasRole('super_admin') || auth()->user()->can('view_all_employee')) {
            return static::getModel()::count();
        } else {
            return static::getModel()::where('created_by', auth()->id())->count();
        }
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where(function ($query) {

            if (auth()->user()?->hasRole('super_admin') || auth()->user()?->can('view_all_employee')
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
                        Forms\Components\Select::make('employee_id')
                            ->label('Personnel')
                            ->placeholder('Sélectionnez un personnel')
                            ->options(Employee::get()->pluck('full_name', 'id'))
                            ->validationMessages([
                                'required' => 'Veuillez sélectionner un personnel.',
                            ])
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->label('Prêt')
                            ->placeholder('Nom du prêt')
                            ->validationMessages([
                                'required' => 'Le nom du prêt est obligatoire.',
                            ])
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
                                    ->default(now())
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

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Personnel')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('employee', function (Builder $query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('surname', 'like', "%{$search}%");
                        });
                    }),
                Tables\Columns\TextColumn::make('name')
                    ->label('Prêt')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Montant')
                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('borrowed_at')
                    ->label('Date d\'Emprunt')
                    ->searchable()
                    ->date('d/m/Y')
                    ->sortable(),
                //                Tables\Columns\TextColumn::make('items_count')
                //                    ->label('Remboursements')
                //                    ->counts('items')
                //                    ->badge()
                //                    ->color("info")
                //                    ->sortable()
                //                    ->toggleable(isToggledHiddenByDefault: false),
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
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->hidden(fn ($record) => $record->trashed()),
                    // ->hidden(fn ($record) => $record->items()->exists() || $record->trashed()),
                ]),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // 'items' => RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDebts::route('/'),
            'create' => Pages\CreateDebt::route('/create'),
            'edit' => Pages\EditDebt::route('/{record}/edit'),
            'view' => Pages\ViewDebts::route('/{record}'),
        ];
    }
}
