<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HolidayResource\Pages;
use App\Models\Holiday;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HolidayResource extends Resource
{
    protected static ?string $model = Holiday::class;

    protected static ?string $navigationIcon = 'heroicon-o-minus-circle';

    protected static ?string $navigationGroup = 'Gestion du Paiement';

    public static function shouldRegisterNavigation(): bool
    {
        return config('module.holidays.enable', true);
    }

    public static function getLabel(): ?string
    {
        return 'Jour Férié';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Jours Fériés';
    }

    public static function getNavigationBadge(): ?string
    {
        if (auth()->user()->hasRole('super_admin') || auth()->user()->can('view_all_holidays')) {
            return static::getModel()::count();
        } else {
            return static::getModel()::where('created_by', auth()->id())->count();
        }
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where(function ($query) {

            if (auth()->user()?->hasRole('super_admin') || auth()->user()?->can('view_all_holidays')
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
                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom du jour férié')
                            ->placeholder('Ex: Fête du Travail')
                            ->validationMessages([
                                'required' => 'Champ obligatoire',
                            ])
                            ->required(),
                        Forms\Components\DatePicker::make('date')
                            ->label('Date du jour férié')
                            ->placeholder('Sélectionner une date')
//                            ->minDate(now())
//                            ->maxDate(now()->addYears(5))
                            ->displayFormat('d/m/Y')
                            ->validationMessages([
                                'required' => 'Champ obligatoire',
                            ])
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom du Jour Férié')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Date du Jour Férié')
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
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->modalHeading('Détails du jour férié')
                        ->mutateFormDataUsing(function (array $data) {
                            $data['updated_by'] = auth()->id();

                            return $data;
                        }),
                    Tables\Actions\EditAction::make()
                        ->modalHeading('Modifier le jour férié')
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
            'index' => Pages\ListHolidays::route('/'),
            // 'create' => Pages\CreateHoliday::route('/create'),
            // 'edit' => Pages\EditHoliday::route('/{record}/edit'),
        ];
    }
}
