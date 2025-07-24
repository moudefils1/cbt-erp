<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getLabel(): ?string
    {
        return 'Poste';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Postes';
    }

    public static function getNavigationBadge(): ?string
    {
        if (auth()->user()->hasRole('super_admin') || auth()->user()->can('view_all_task')) {
            return static::getModel()::count();
        } else {
            return static::getModel()::where('created_by', auth()->id())->count();
        }
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where(function ($query) {

            if (auth()->user()?->hasRole('super_admin') || auth()->user()?->can('view_all_task')
            ) {
                return $query;
            }

            return $query->where('created_by', auth()->id());
        });
    }

    protected static ?string $navigationGroup = 'Gestion des Personnels';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du Poste')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->placeholder('Nom du poste')
                            ->required(),
                        Forms\Components\Select::make('location_id')
                            ->relationship('location', 'name')
                            ->label('Departement')
                            ->placeholder('Selectionner un departement')
                            ->searchable()
                            ->preload()
                            ->validationMessages([
                                'required' => 'Veuillez selectionner un departement',
                            ])
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom du Poste')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Département')
                    ->searchable()
                    ->sortable(),
                //                Tables\Columns\TextColumn::make('is_available')
                //                    ->label('Disponibilité')
                //                    ->badge()
                //                    ->color(fn ($record) => match ($record->is_available->value) {
                //                        0 => 'danger',
                //                        1 => 'success',
                //                    }),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->visible(fn ($record) => auth()->user()->hasRole('super_admin'))
                    ->label('Créé par')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->date('d/m/Y H:i:s'),
                Tables\Columns\TextColumn::make('updatedBy.name')
                    ->label('Modifié par')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->getStateUsing(fn ($record) => $record->updated_by ? $record->updated_at : null)
                    ->label('Modifié le')
                    ->alignEnd()
                    ->date('d/m/Y H:i:s'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('activities')
                        ->label('Historiques')
                        ->icon('heroicon-o-clock')
                        ->url(fn ($record) => TaskResource::getUrl('activities', ['record' => $record]))
                        ->hidden(fn ($record) => $record->trashed()),
                    Tables\Actions\ViewAction::make()
                        ->hidden(fn ($record) => $record->trashed()),
                    Tables\Actions\EditAction::make()
                        ->hidden(fn ($record) => $record->trashed() || $record->is_available == 0),
                    Tables\Actions\DeleteAction::make()
                        ->hidden(fn ($record) => $record->employees()->exists() || $record->trashed()),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ForceDeleteAction::make()
                        ->visible(fn ($record) => $record->trashed() && auth()->user()->hasRole('super_admin')),
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
            'employees' => RelationManagers\EmployeesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            // 'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
            'view' => Pages\ViewTask::route('/{record}'),
            'activities' => Pages\ListTaskActivities::route('/{record}/activities'),
        ];
    }
}
