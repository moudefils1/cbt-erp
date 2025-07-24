<?php

namespace App\Filament\Resources;

use App\Enums\OperationEnum;
use App\Enums\PartEnum;
use App\Filament\Resources\PayrollDesignationResource\Pages;
use App\Models\PayrollDesignation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PayrollDesignationResource extends Resource
{
    protected static ?string $model = PayrollDesignation::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-4';

    protected static ?string $navigationGroup = 'Gestion du Paiement';

    protected static ?int $navigationSort = 1;

    protected static bool $shouldRegisterNavigation = false;

    public static function getLabel(): ?string
    {
        return 'Désignation';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Désignations';
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
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->placeholder('Nom de la désignation')
                            ->validationMessages([
                                'required' => 'Le nom est obligatoire',
                            ])
                            ->columnSpanFull()
                            ->required(),
                        Forms\Components\Select::make('part')
                            ->label('Part')
                            ->placeholder('Sélectionnez')
                            ->options(PartEnum::class)
                            ->required(),
                        Forms\Components\Select::make('operation')
                            ->label('Opération')
                            ->placeholder('Sélectionnez')
                            ->options(OperationEnum::class)
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom de la désignation')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('part')
                    ->label('Part')
                    ->sortable(),
                Tables\Columns\TextColumn::make('operation')
                    ->label('Opération')
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
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListPayrollDesignations::route('/'),
            // 'create' => Pages\CreatePayrollDesignation::route('/create'),
            // 'edit' => Pages\EditPayrollDesignation::route('/{record}/edit'),
            // 'view' => Pages\ViewPayrollDesignations::route('/{record}'),
        ];
    }
}
