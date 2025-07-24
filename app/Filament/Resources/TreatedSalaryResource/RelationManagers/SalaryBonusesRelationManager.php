<?php

namespace App\Filament\Resources\TreatedSalaryResource\RelationManagers;

use App\Enums\StatusEnum;
use App\Models\SalaryBonus;
use App\Models\TreatedSalary;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Components\Tab;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SalaryBonusesRelationManager extends RelationManager
{
    protected static string $relationship = 'salaryBonuses';

    protected static ?string $title = 'Primes';
    protected static ?string $label = 'Prime';
    protected static ?string $pluralLabel = 'Primes';
    protected static ?string $icon = 'heroicon-o-banknotes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('employee_id')
                    ->default(fn (RelationManager $livewire) => $livewire->ownerRecord->employee_id),
                Forms\Components\Fieldset::make('Détails de la Prime')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->placeholder('Nom de la prime')
                            ->validationMessages([
                                'required' => 'Le nom de la prime est obligatoire.',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Montant')
                            ->placeholder('Montant de la prime')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        Forms\Components\Fieldset::make('Description')
                            ->schema([
                                Forms\Components\Textarea::make('description')
                                    ->hiddenLabel()
                                    ->placeholder('Description de la prime')
                                    ->columnSpanFull(),
                            ])->columns(1),
                    ])->columns(2),
                Forms\Components\Fieldset::make('Statut')
                    ->hiddenOn('create')
                    ->schema([
                        Forms\Components\Toggle::make('status')
                            ->label('Statut')
                            ->helperText('Rouge = Inactif, Vert = Actif')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->required(),
                    ])->columns(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('name')->label('Prime'),
                TextColumn::make('amount')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, ',', ' ') . ' CFA'),
                TextColumn::make('status'),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading('Ajouter une Prime')
                    ->mutateFormDataUsing(function (array $data) {
                        $data['created_by'] = auth()->id();

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Modifier la Prime')
                    ->mutateFormDataUsing(function (array $data) {
                        $data['updated_by'] = auth()->id();

                        return $data;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->action(function ($record) {
                        $record->update(['deleted_by' => auth()->id()]);
                        $record->delete();
                    }),
            ]);
    }

    protected function getTableQuery(): Builder
    {
        return SalaryBonus::query()
            ->where('employee_id', $this->ownerRecord->employee_id);
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function getTabs(): array
    {
        $tabs = [];

        $tabs['active_amount'] = Tab::make('Total Actif')
            ->badge(fn () => number_format($this->ownerRecord->salaryBonuses?->where('status', StatusEnum::ACTIVE)->sum('amount'), 2) . ' CFA')
            ->badgeIcon('heroicon-o-banknotes')
            ->badgeColor('success');

        $tabs['inactive_amount'] = Tab::make('Total Inactif')
            ->badge(fn () => number_format($this->ownerRecord->salaryBonuses?->where('status', StatusEnum::INACTIVE)->sum('amount'), 2) . ' CFA')
            ->badgeIcon('heroicon-o-banknotes')
            ->badgeColor('danger');

        return $tabs;
    }
}
