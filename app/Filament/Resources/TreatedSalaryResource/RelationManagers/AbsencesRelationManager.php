<?php

namespace App\Filament\Resources\TreatedSalaryResource\RelationManagers;

use App\Enums\AbsenceStatusEnum;
use App\Models\Absence;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AbsencesRelationManager extends RelationManager
{
    protected static string $relationship = 'absences';

    protected static ?string $title = 'Pointages';

    protected static ?string $modelLabel = 'Pointage';

    protected static ?string $pluralModelLabel = 'Pointages';

    protected static ?string $icon = 'heroicon-o-clock';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Date de l\'absence')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
            ])
            ->filters([
                //
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
            ]);
    }

    protected function getTableQuery(): Builder
    {
        return Absence::query()
            ->where('employee_id', $this->ownerRecord->employee_id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->orderBy('date', 'desc');
    }

    protected function canCreate(): bool
    {
        return false;
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
