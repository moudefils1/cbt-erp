<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TreatedSalaryResource\Pages;
use App\Filament\Resources\TreatedSalaryResource\RelationManagers\AbsencesRelationManager;
use App\Filament\Resources\TreatedSalaryResource\RelationManagers\SalaryBonusesRelationManager;
use App\Models\TreatedSalary;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TreatedSalaryResource extends Resource
{
    protected static ?string $model = TreatedSalary::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Gestion du Paiement';

    public static function shouldRegisterNavigation(): bool
    {
        return config('module.treated_salaries.enable', true);
    }

    public static function getModelLabel(): string
    {
        return "Traitement de Salaire";
    }

    public static function getPluralModelLabel(): string
    {
        return "Traitements de Salaires";
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereMonth('treatment_date', now()->month)
            ->whereYear('treatment_date', now()->year)
            ->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('employee_id')
                    ->relationship('employee', 'full_name')
                    ->searchable()
                    ->required(),
                DatePicker::make('treatment_date')->required(),
                DatePicker::make('start_date')->required(),
                DatePicker::make('end_date')->required(),
                TextInput::make('total_working_days')->numeric()->required(),
                TextInput::make('actual_working_days')->numeric()->required(),
                TextInput::make('total_working_hours')->numeric()->required(),
                TextInput::make('actual_working_hours')->numeric()->required(),
                TextInput::make('hourly_rate')->numeric()->required()->prefix('FCFA'),
                TextInput::make('base_salary')->numeric()->required()->prefix('FCFA'),
                TextInput::make('total_bonuses')->numeric()->required()->prefix('FCFA'),
                TextInput::make('total_deductions')->numeric()->required()->prefix('FCFA'),
                TextInput::make('final_salary')->numeric()->required()->prefix('FCFA'),
                Repeater::make('bonus_details')
                    ->schema([
                        TextInput::make('name')->label('Bonus Name'),
                        TextInput::make('amount')->numeric()->label('Amount')->prefix('FCFA'),
                    ])->label('Bonuses'),
                Repeater::make('deduction_details')
                    ->schema([
                        TextInput::make('name')->label('Deduction Name'),
                        TextInput::make('amount')->numeric()->label('Amount')->prefix('FCFA'),
                        TextInput::make('type')->label('Type'),
                    ])->label('Deductions'),
                Textarea::make('notes'),
                Toggle::make('is_paid')->label('Paid'),
                DatePicker::make('paid_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.full_name')->label('Personnel')->searchable(),
//                TextColumn::make('start_date')->label('Début de période')->date('d/m/Y')->sortable(),
//                TextColumn::make('end_date')->label('Fin de période')->date('d/m/Y')->sortable(),
                TextColumn::make('treatment_date')->label('Date de traitement')->date('d/m/Y')->sortable(),
                TextColumn::make('employee.basic_salary')
                    ->label('Salaire de base')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, ',', ' ') . ' CFA')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('base_salary')
                    ->label('Salaire aquis')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, ',', ' ') . ' CFA')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('total_bonuses')
                    ->label('Primes')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, ',', ' ') . ' CFA')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('total_deductions')
                    ->label('Prélevements')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, ',', ' ') . ' CFA')
                    ->badge()
                    ->color('danger')
                    ->sortable(),
                TextColumn::make('final_salary')
                    ->label('Net à payer')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, ',', ' ') . ' CFA')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_paid')->label('Payé'),
                TextColumn::make('created_at')->dateTime()->label('Créé le')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Add filters as needed
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                ])
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SalaryBonusesRelationManager::class,
            AbsencesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTreatedSalaries::route('/'),
            'create' => Pages\CreateTreatedSalary::route('/create'),
            'edit' => Pages\EditTreatedSalary::route('/{record}/edit'),
            'view' => Pages\ViewTreatedSalary::route('/{record}'),
        ];
    }
}
