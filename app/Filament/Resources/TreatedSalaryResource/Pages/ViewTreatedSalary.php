<?php

namespace App\Filament\Resources\TreatedSalaryResource\Pages;

use App\Actions\SalaryTreatmentAction;
use App\Enums\SalaryDeductionTypeEnum;
use App\Filament\Resources\TreatedSalaryResource;
use App\Filament\Resources\TreatedSalaryResource\RelationManagers\SalaryBonusesRelationManager;
use App\Models\Employee;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;

class ViewTreatedSalary extends ViewRecord
{
    protected static string $resource = TreatedSalaryResource::class;

    protected ?string $heading = 'Détails du Traitement de Salaire';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download_pdf')
                ->label('Télécharger la fiche de paie')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn ($record) => route('salary-slip.download', $record)),

            // Action to deduct salary
            Actions\Action::make('deduct_salary')
                ->visible(function ($record) {
                    // toplam borç miktarı
                    $totalDebt = $record->employee->debts()
                        ->where('is_paid', false)
                        ->sum('amount');

                    // toplam ödenen miktar
                    $totalPaid = $record->employee->debtItems()
                        ->sum('paid_amount');

                    // kalan borç miktarı
                    $remainingDebt = max(0, $totalDebt - $totalPaid);

                    // Eğer kalan borç miktarı 0 ise, aksiyon görünmez olsun
                    return $remainingDebt > 0 && !$record->is_paid;
                })
                ->label('Faire un prélèvement')
                ->icon('heroicon-o-minus-circle')
                ->form([
                    \Filament\Forms\Components\TextInput::make('current_salary')
                        ->label('Net à Payer')
                        ->default(fn ($record) => $record->final_salary)
                        ->formatStateUsing(fn ($state) => number_format($state, 2) . ' XAF')
                        ->disabled(),
                    \Filament\Forms\Components\TextInput::make('total_deductions')
                        ->label('Total Emprunt')
                        ->default(fn ($record) => $record->employee->debts()
                            ->where('is_paid', false)
                            ->sum('amount'))
                        ->formatStateUsing(fn ($state) => number_format($state, 2) . ' XAF')
                        ->disabled(),
                    \Filament\Forms\Components\TextInput::make('total_recoveries')
                        ->label('Recouvrements')
                        ->default(fn ($record) => $record->employee->debtItems()
                            ->sum('paid_amount'))
                        ->formatStateUsing(fn ($state) => number_format($state, 2) . ' XAF')
                        ->disabled(),
                    \Filament\Forms\Components\TextInput::make('remaining_debt')
                        ->label('Dette Restante')
                        ->default(fn ($record) => max(
                            0,
                            $record->employee->debts()
                                ->where('is_paid', false)
                                ->sum('amount') - $record->employee->debtItems()
                                ->sum('paid_amount')
                        ))
                        ->formatStateUsing(fn ($state) => number_format($state, 2) . ' XAF')
                        ->disabled(),
                    \Filament\Forms\Components\TextInput::make('deduction_amount')
                        ->label('Taux de Prélevement')
                        ->placeholder('Entrez le montant a prélever')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(function ($record) {
                            $finalSalary = $record->final_salary ?? 0;

                            // Toplam borç
                            $totalDebt = $record->employee->debts()
                                ->where('is_paid', false)
                                ->sum('amount');

                            // Daha önceki ödemeler
                            $totalPaid = \App\Models\DebtItem::where('employee_id', $record->employee_id)
                                ->sum('paid_amount');

                            $remainingDebt = max(0, $totalDebt - $totalPaid);

                            return min($finalSalary, $remainingDebt);
                        })
                        ->validationMessages([
                            'max' => 'Le montant ne doit pas dépasser le salaire net ou la dette totale.',
                            'min' => 'Le montant doit être supérieur ou égal à zéro.',
                            'required' => 'Le montant est obligatoire.',
                            'numeric' => 'Le montant doit être un nombre.',
                        ])
                        ->required(),
                ])
                ->action(function (array $data) {
                    $treatedSalary = $this->record;
                    $employee = $treatedSalary->employee;

                    DB::transaction(function () use ($data, $employee) {
                        $deductionAmount = $data['deduction_amount'];

                        // 1. DebtItem olarak ödeme kaydet
                        \App\Models\DebtItem::create([
                            'employee_id' => $employee->id,
                            'paid_amount' => $deductionAmount,
                            'paid_at' => now(),
                            'created_by' => auth()->id(),
                        ]);

                        // 2. Tüm borçları kontrol et
                        $totalDebt = $employee->debts()
                            ->where('is_paid', false)
                            ->sum('amount');

                        $totalPaid = \App\Models\DebtItem::where('employee_id', $employee->id)
                            ->sum('paid_amount');

                        // 3. Eğer toplam ödeme toplam borcu karşıladıysa tüm borçları "is_paid = true" yap
                        if ($totalPaid >= $totalDebt) {
                            $employee->debts()
                                ->where('is_paid', false)
                                ->update([
                                    'is_paid' => true,
                                    'paid_at' => now(),
                                ]);
                        }
                    });

                    Notification::make()
                        ->title('Prélevement de salaire')
                        ->success()
                        ->body('Le salaire a été prélevé et les dettes mises à jour.')
                        ->send();
                })
                ->requiresConfirmation()
                ->color('danger'),

            Actions\Action::make('re-generate')
                ->label('Re-traiter le salaire')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    // Get the current record
                    $model = Employee::find($this->record->employee_id);

                    // Check if the record is already paid
                    (new SalaryTreatmentAction($model))->handle();

                    Notification::make()
                        ->title('Salary Slip Re-generated')
                        ->success()
                        ->body('The salary slip has been re-generated successfully.')
                        ->send();
                })
                ->requiresConfirmation()
                ->color('warning'),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            'salaryBonuses' => SalaryBonusesRelationManager::class,
            'absences' => TreatedSalaryResource\RelationManagers\AbsencesRelationManager::class,
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Details de Traitement')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('employee.full_name')
                                    ->label('Personnel'),
                                TextEntry::make('start_date')
                                    ->date('d/m/Y')
                                    ->label('Début de période'),
                                TextEntry::make('end_date')
                                    ->date('d/m/Y')
                                    ->label('Fin de période'),
                                TextEntry::make('treatment_date')
                                    ->date('d/m/Y')
                                    ->label('Date de traitement'),
                                TextEntry::make('final_salary')
                                    ->label('Net à payer')
                                    ->formatStateUsing(fn ($state) => number_format($state, 2, ',', ' ') . ' CFA')
                                    ->badge()
                                    ->color('success'),
                                TextEntry::make('is_paid')
                                    ->label('Payé')
                                    ->badge()
                                    ->color(fn ($state) => $state ? 'success' : 'danger')
                                    ->formatStateUsing(fn ($state) => $state ? 'Oui' : 'Non')
                            ]),
                    ]),
                Section::make('Horaires de travail')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('total_working_days')->label('Jours ouvrables'),
                                TextEntry::make('total_working_hours')->label('Heures ouvrables'),
                                TextEntry::make('actual_working_days')->label('Jours travaillés'),
                                TextEntry::make('actual_working_hours')->label('Heures travaillées'),
                                TextEntry::make('hourly_rate')
                                    ->label('Salaire par heure')
                                    ->formatStateUsing(fn ($state) => number_format($state, 2, ',', ' ') . ' CFA')
                                    ->badge()
                                    ->color('primary'),
                            ])->columns(5),
                    ]),
                Section::make('Primes')
                    ->schema([
                        RepeatableEntry::make('bonus_details')
                            ->hiddenLabel()
                            ->columns(2)
                            ->schema([
                                TextEntry::make('name')->label('Nom'),
                                TextEntry::make('amount')
                                    ->label('Montant')
                                    ->formatStateUsing(fn ($state) => number_format($state, 2, ',', ' ') . ' CFA')
                                    ->badge()
                                    ->color('primary'),
                            ]),
                    ]),
                Section::make('Prélevements')
                    ->schema([
                        RepeatableEntry::make('deduction_details')
                            ->hiddenLabel()
                            ->columns(3)
                            ->schema([
                                TextEntry::make('name')->label('Nom'),
                                TextEntry::make('amount')
                                    ->label('Montant')
                                    ->formatStateUsing(fn ($state) => number_format($state, 2, ',', ' ') . ' CFA')
                                    ->badge()
                                    ->color('danger'),
                                TextEntry::make('type')->label('Type de prélèvement'),
                            ]),
                    ]),
                Section::make('Notes')
                    ->schema([
                        TextEntry::make('notes'),
                    ]),
            ]);
    }
}
