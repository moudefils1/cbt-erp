<?php

namespace App\Filament\Resources;

use App\Enums\AbsenceStatusEnum;
use App\Filament\Resources\AbsenceResource\Pages;
use App\Models\Absence;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AbsenceResource extends Resource
{
    protected static ?string $model = Absence::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Gestion des Personnels';

    public static function shouldRegisterNavigation(): bool
    {
        return config('module.absences.enable', true);
    }

    /**
     * @return string
     */
    public static function getModelLabel(): string
    {
        return "Pointage";
    }

    public static function getPluralModelLabel(): string
    {
        return "Pointages";
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('date', today())->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Date de l\'absence')
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Nom du personnel')
                    ->searchable(['name', 'surname']),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
            ])->filters([
            ])
            ->actions([
                Tables\Actions\Action::make('markAsAbsent')
                    ->label('Marquer comme absent')
                    ->action(function (Absence $record) {
                        $record->update([
                            'status' => AbsenceStatusEnum::ABSENT,
                            'is_present' => false,
                        ]);

                        Notification::make()
                            ->title('Employé marqué comme absent')
                            ->success()
                            ->send();
                    })
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->color('danger')
                    ->button()
                    ->visible(fn (Absence $record): bool => $record->status->is(AbsenceStatusEnum::PRESENT)),

                Tables\Actions\Action::make('markAsPresent')
                    ->label('Marquer comme présent')
                    ->action(function (Absence $record) {
                        $record->update([
                            'status' => AbsenceStatusEnum::PRESENT,
                            'is_present' => true,
                        ]);

                        Notification::make()
                            ->title('Employé marqué comme présent')
                            ->success()
                            ->send();
                    })
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->color('success')
                    ->button()
                    ->visible(fn (Absence $record): bool => $record->status->is(AbsenceStatusEnum::ABSENT)),
            ])
            ->bulkActions([
                //
            ])
            ->filters([

                // show all records for today and user can filter by date
                Tables\Filters\SelectFilter::make('date')
                    ->label('Date')
                    ->options(function () {
                        $range = now()->setDay(1)->daysUntil(now());
                        $dates = [];
                        foreach ($range as $date) {
                            $dates[$date->format('Y-m-d')] = $date->format('d/m/Y');
                        }

                        return array_reverse($dates);
                    })
                    ->default(now()->format('Y-m-d')),
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
            'index' => Pages\ListAbsences::route('/'),
            'create' => Pages\CreateAbsence::route('/create'),
            'edit' => Pages\EditAbsence::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year);
    }
}
