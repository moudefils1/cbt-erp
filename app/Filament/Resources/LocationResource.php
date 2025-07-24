<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LocationResource\Pages;
use App\Filament\Resources\LocationResource\RelationManagers;
use App\Models\Location;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    public static function getLabel(): ?string
    {
        return 'Département';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Départements';
    }

    public static function getNavigationBadge(): ?string
    {
        if (auth()->user()->hasRole('super_admin') || auth()->user()->can('view_all_location')) {
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

    protected static ?string $navigationGroup = 'Gestion des Personnels';

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du Departement')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->placeholder('Nom du departement')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom du Departement')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tasks_count')
                    ->label('Postes')
                    ->counts('tasks')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->visible(fn ($record) => auth()->user()->hasRole('super_admin'))
                    ->label('Créé par')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->searchable()
                    ->sortable()
                    ->date('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('updatedBy.name')
                    ->label('Modifié par')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->getStateUsing(fn ($record) => $record->updated_by ? $record->updated_at : null)
                    ->label('Modifié le')
                    ->searchable()
                    ->sortable()
                    ->alignEnd()
                    ->date('d/m/Y H:i'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('activities')
                        ->label('Historiques')
                        ->icon('heroicon-o-clock')
                        ->url(fn ($record) => LocationResource::getUrl('activities', ['record' => $record]))
                        ->hidden(fn ($record) => $record->trashed()),
                    Tables\Actions\ViewAction::make()
                        ->hidden(fn (Location $location) => $location->trashed()),
                    Tables\Actions\EditAction::make()
                        ->hidden(fn (Location $location) => $location->trashed()),
                    Tables\Actions\DeleteAction::make()
                        ->hidden(fn (Location $location) => $location->tasks()->exists() || $location->trashed()),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            'tasks' => RelationManagers\TasksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLocations::route('/'),
            // 'create' => Pages\CreateLocation::route('/create'),
            'edit' => Pages\EditLocation::route('/{record}/edit'),
            'view' => Pages\ViewLocation::route('/{record}'),
            'activities' => Pages\ListLocationActivities::route('/{record}/activities'),
        ];
    }
}
