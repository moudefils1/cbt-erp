<?php

namespace App\Filament\Clusters\Enter\Resources\InvoiceResource\RelationManagers;

use App\Enums\PaymentMethodEnum;
use Filament\Forms;
use Filament\Resources\Components\Tab;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    protected static ?string $label = 'Paiement';
    protected static ?string $title = 'Paiements';
    protected static ?string $icon = 'heroicon-o-credit-card';

    /**
     * @return string|null
     */
    public static function getModelLabel(): ?string
    {
        return "Paiements";
    }
    /**
     * @return string|null
     */
    public static function getPluralModelLabel(): ?string
    {
        return "Paiements";
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('Informations de Paiement')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('invoice.amount')
                            ->label('Montant de la Facture')
                            ->default(fn () => $this->ownerRecord->amount)
                            //->formatStateUsing(fn ($state) => number_format($state, 2, ',', ' ') . ' XAF')
                            ->formatStateUsing(fn ($state) => number_format($state, 2) . ' XAF')
                            ->disabled(),
                    ]),
            ])
            ->schema([
                Forms\Components\TextInput::make('amount')
                    ->label('Montant')
                    ->required()
                    ->numeric()
                    ->maxValue(fn () => $this->ownerRecord->amount - $this->ownerRecord->payments()->sum('amount'))
                    ->validationMessages([
                        'required' => 'Le montant est requis.',
                        'numeric' => 'Le montant doit être un nombre.',
                        'max_value' => 'Le montant ne peut pas dépasser le montant restant à payer.',
                    ]),
                Forms\Components\Select::make('payment_method')
                    ->label('Méthode de Paiement')
                    ->enum(PaymentMethodEnum::class)
                    ->options(PaymentMethodEnum::class)
                    ->live()
                    ->required(),
                Forms\Components\TextInput::make('payment_reference')
                    ->hidden(fn ($get) => $get('payment_method') == PaymentMethodEnum::CASH->value)
                    ->label('Référence de Paiement')
                    ->placeholder('Numéro de Compte ou Numéro de Chèque')
                    ->helperText("Pour virement et chèque, indiquez le numéro de compte ou le numéro de chèque.")
                    ->maxLength(255)
                    ->required(fn ($get) => $get('payment_method') != PaymentMethodEnum::CASH->value)
                    ->validationMessages([
                        'max' => 'La référence de paiement ne peut pas dépasser 255 caractères.',
                    ]),
                Forms\Components\DatePicker::make('payment_date')
                    ->label('Date de Paiement')
                    ->default(now())
                    ->minDate(fn () => $this->ownerRecord->created_at->format('Y-m-d'))
                    ->maxDate(now())
                    ->required()
                    ->validationMessages([
                        'required' => 'La date de paiement est requise.',
                    ]),
                Forms\Components\SpatieMediaLibraryFileUpload::make('payment_file')
                    ->label('Fiche de Paiement')
                    ->collection('payment_files')
                    ->visibility('public')
                    ->acceptedFileTypes(['image/*', 'application/pdf'])
                    ->maxSize(1024 * 5) // 5 MB
                    ->openable()
                    ->downloadable()
                    ->helperText('Téléchargez une image ou un PDF du paiement.')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->maxLength(500)
                    ->validationMessages([
                        'max' => 'Les notes ne peuvent pas dépasser 500 caractères.',
                    ])
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('amount')
                    ->label('Montant')
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' CFA')
                    ->badge()
                    ->color("success")
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Méthode de Paiement')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_reference')
                    ->label('Référence de Paiement')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Date de Paiement')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->searchable(),
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
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Méthode de Paiement')
                    ->options(PaymentMethodEnum::class)
                    ->searchable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->hidden(fn () => $this->ownerRecord->amount <= $this->ownerRecord->payments()->sum('amount'))
                    ->mutateFormDataUsing(function (array $data) {
                        $data['created_by'] = auth()->id();

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->mutateFormDataUsing(function (array $data) {
                            $data['updated_by'] = auth()->id();

                            return $data;
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->before(function ($record) {
                            $record->deleted_by = auth()->id();
                            $record->save();
                        })
                        ->requiresConfirmation(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function getTabs(): array
    {
        $tabs = [];

        $tabs['amount'] = Tab::make('Total A Payer')
            ->badge(fn () => number_format($this->ownerRecord->amount, 2) . ' CFA')
            ->badgeIcon('heroicon-o-banknotes')
            ->badgeColor('primary');

        $totalPaid = $this->ownerRecord->payments()->sum('amount');

        $tabs['paid'] = Tab::make('Total Payé')
            ->badge(fn () => number_format($totalPaid, 2) . ' CFA')
            ->badgeIcon('heroicon-o-check-circle')
            ->badgeColor('success');

        $remainingAmount = max(0, $this->ownerRecord->amount - $totalPaid);

        $tabs['remaining'] = Tab::make('Reste à Payer')
            ->badge(fn () => number_format($remainingAmount, 2) . ' CFA')
            ->badgeIcon('heroicon-o-exclamation-circle')
            ->badgeColor('warning');

        return $tabs;
    }
}
