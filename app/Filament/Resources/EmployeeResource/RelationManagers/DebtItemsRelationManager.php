<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DebtItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'debtItems';

    protected static ?string $title = 'Remboursements';

    protected static ?string $label = 'Remboursement';

    protected static ?string $pluralLabel = 'Remboursements';

    protected static ?string $icon = 'heroicon-o-banknotes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('Détails du Remboursement')
                    ->schema([
                        Forms\Components\TextInput::make('paid_amount')
                            ->label('Montant Remboursé')
                            ->placeholder('Montant remboursé')
                            ->numeric()
                            ->minValue(0)
                            ->validationMessages([
                                'required' => 'Le montant remboursé est obligatoire.',
                                'numeric' => 'Le montant remboursé doit être un nombre.',
                                'min' => 'Le montant remboursé doit être un nombre positif.',
                            ])
                            // ->readOnly(fn ($record) => $record && $record->items()->exists())
                            ->required(),
                        Forms\Components\DatePicker::make('paid_at')
                            ->label('Date de Remboursement')
                            ->placeholder('Date de remboursement')
                            ->validationMessages([
                                'required' => 'La date de remboursement est obligatoire.',
                            ])
                            // ->readOnly(fn ($record) => $record && $record->items()->exists())
                            ->default(now())
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Description du remboursement')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            // ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Date de Remboursement')
                    ->date('d/m/Y')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Montant Remboursé')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, ',', ' ') . ' CFA')
                    ->badge()
                    ->color('success')
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
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading('Ajouter un remboursement')
                    ->mutateFormDataUsing(function (array $data) {
                        $data['created_by'] = auth()->id();

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->action(function ($record) {
                            DB::beginTransaction();

                            try {
                                // check the employee
                                $employee = $record->employee()->with(['debts', 'debtItems'])->first();

                                $totalDebtAmount = $employee->debts()
                                    ->whereNull('deleted_at')
                                    ->sum('amount');

                                $totalDebtItemPaidAmount = $employee->debtItems()
                                    ->whereNull('deleted_at')
                                    ->where('id', '!=', $record->id)
                                    ->sum('paid_amount');

                                // kalan borç miktarını hesapla
                                $remainingAmount = $totalDebtAmount - $totalDebtItemPaidAmount;

                                if ($remainingAmount > 0) {
                                    $employee->debts()
                                        ->where('is_paid', true)
                                        ->whereNull('deleted_at')
                                        ->update([
                                            'is_paid' => false,
                                        ]);
                                } else {
                                    $employee->debts()
                                        ->where('is_paid', false)
                                        ->whereNull('deleted_at')
                                        ->update([
                                            'is_paid' => true,
                                        ]);
                                }

                                $record->delete();

                                DB::commit();

                                Notification::make()
                                    ->title('Remboursement supprimé avec succès')
                                    ->success()
                                    ->send();

                            } catch (\Exception $e) {
                                DB::rollBack();

                                // log the error
                                Log::error('Erreur lors de la suppression du remboursement : ' . $e->getMessage(), [
                                    'record_id' => $record->id,
                                    'employee_id' => $record->employee_id,
                                    'user_id' => auth()->id(),
                                ]);

                                Notification::make()
                                    ->title('Erreur lors de la suppression')
                                    ->body('Une erreur est survenue lors de la suppression du remboursement')
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->bulkActions([
                //
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function getTabs(): array
    {
        $tabs = [];

        $tabs['paid_amount'] = Tab::make('Total Remboursé')
            ->badge(fn () => number_format($this->ownerRecord->debtItems?->sum('paid_amount'), 2) . ' CFA')
            ->badgeIcon('heroicon-o-banknotes')
            ->badgeColor('success');

        return $tabs;
    }
}
