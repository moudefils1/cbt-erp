<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GradeResource\Pages;
use App\Filament\Resources\GradeResource\RelationManagers;
use App\Models\Grade;
use App\Models\Intern;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GradeResource extends Resource
{
    protected static ?string $model = Grade::class;

    public static function getModelLabel(): string
    {
        return 'Niveau';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Niveaux';
    }

    protected static ?string $navigationGroup = 'Gestion des Personnels';

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    //protected static ?int $navigationSort = 1000;

    public static function getNavigationBadge(): ?string
    {
        if (auth()->user()->hasRole('super_admin') || auth()->user()->can('view_all_grade')) {
            return static::getModel()::count();
        } else {
            return static::getModel()::where('created_by', auth()->id())->count();
        }
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where(function ($query) {
            if (auth()->user()?->hasRole('super_admin') || auth()->user()?->can('view_all_grade')) {
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
                        Forms\Components\Fieldset::make('Informations du Niveau')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nom du Niveau')
                                    ->placeholder('Ex: Licence, Master, etc.')
                                    ->string()
                                    ->maxLength(255)
                                    ->required(),
                                Forms\Components\Fieldset::make('Description')
                                    ->schema([
                                        Forms\Components\Textarea::make('description')
                                            ->hiddenLabel()
                                            ->placeholder('Décrivez le niveau en quelques mots (optionnel)')
                                            ->string()
                                            ->maxLength(255)
                                            ->columnSpan('full'),
                                    ])->columnSpanFull(),
                            ])->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom du Niveau'),
                Tables\Columns\TextColumn::make('interns_count')
                    ->visible(fn () => auth()->user()->can('viewAny', Intern::class))
                    ->label('Stagiaires')
                    ->counts('interns')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('employees_count')
                    ->label('Personnels')
                    ->counts('employees')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->date('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Dernière modification')
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
                        ->url(fn ($record) => GradeResource::getUrl('activities', ['record' => $record]))
                        ->hidden(fn ($record) => $record->trashed()),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->hidden(fn ($record) => $record->interns()->exists() || $record->employees()->exists()),
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
            RelationManagers\GradeInternsRelationManager::class,
            RelationManagers\GradeEmployeesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGrades::route('/'),
            // 'create' => Pages\CreateGrade::route('/create'),
            'edit' => Pages\EditGrade::route('/{record}/edit'),
            'activities' => Pages\ListGardeActivities::route('/{record}/activities'),
            'view' => Pages\GradeView::route('/{record}'),
        ];
    }
}
