<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveTypeResource\Pages;
use App\Filament\Resources\LeaveTypeResource\RelationManagers;
use App\Models\LeaveType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeaveTypeResource extends Resource
{
    protected static ?string $model = LeaveType::class;

    protected static ?string $navigationIcon = 'heroicon-o-numbered-list';

    protected static ?string $navigationGroup = 'Gestion des Personnels';

    //protected static ?int $navigationSort = 11;

    // protected static bool $shouldRegisterNavigation = false;

    public static function getLabel(): ?string
    {
        return 'Types de Congés';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Types de Congés';
    }

    public static function getNavigationBadge(): ?string
    {
        if (auth()->user()->hasRole('super_admin') || auth()->user()->can('view_all_leave_type')) {
            return static::getModel()::count();
        } else {
            return static::getModel()::where('created_by', auth()->id())->count();
        }
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where(function ($query) {

            if (auth()->user()?->hasRole('super_admin') || auth()->user()?->can('view_all_leave_type')
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
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Fieldset::make('Type de Congé')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nom')
                                    ->placeholder('Nom du type de congé')
                                    ->validationMessages([
                                        'required' => 'Champ obligatoire',
                                    ])
                                    ->columnSpanFull()
                                    ->required(),
                                //                                Forms\Components\TextInput::make('days')
                                //                                    ->label('Nombre de jours')
                                //                                    ->placeholder('Nombre de jours de congé par an')
                                //                                    ->numeric()
                                //                                    ->default(0)
                                //                                    ->minValue(0)
                                //                                    ->validationMessages([
                                //                                        'required' => 'Champ obligatoire',
                                //                                        'numeric' => 'Veuillez saisir un nombre',
                                //                                        'min' => 'Veuillez saisir un nombre positif',
                                //                                    ])
                                //                                    ->required(),
                                Forms\Components\Textarea::make('description')
                                    ->label('Description')
                                    ->placeholder('Description du type de congé')
                                    ->columnSpanFull(),
                                Forms\Components\Toggle::make('is_paid')
                                    ->label('Payé')
                                    ->default(false)
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->helperText('Verte: Congé payé, Rouge: Congé non payé'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom du Type de Congé')
                    ->searchable()
                    ->sortable(),
                //                Tables\Columns\TextColumn::make('days')
                //                    ->label('Nombre de Jours')
                //                    ->badge()
                //                    ->color('info')
                //                    ->searchable()
                //                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_paid')
                    ->label('Payé')
                    ->onColor('success')
                    ->offColor('danger')
                    ->disabled(fn ($record) => ! auth()->user()->hasRole('super_admin') && ($record->leaves()->exists() || $record->employeeLeaveBalances()->exists() || ! auth()->user()->can('update_leave::type')))
                    ->sortable(),
                //                Tables\Columns\TextColumn::make('leaves_count')
                //                    ->counts("leaves")
                //                    ->label('Congés')
                //                    ->badge()
                //                    ->color('info')
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
                Tables\Filters\SelectFilter::make('is_paid')
                    ->label('Payé')
                    ->options([
                        '1' => 'Oui',
                        '0' => 'Non',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->hidden(fn ($record) => $record->leaves()->exists() || $record->employeeLeaveBalances()->exists() || $record->trashed()),
                ]),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\EmployeeLeaveBalancesRelationManager::class,
            RelationManagers\LeavesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveTypes::route('/'),
            'create' => Pages\CreateLeaveType::route('/create'),
            'edit' => Pages\EditLeaveType::route('/{record}/edit'),
            'view' => Pages\ViewLeaveType::route('/{record}'),
        ];
    }
}
