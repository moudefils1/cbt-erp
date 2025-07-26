<?php

namespace App\Filament\Pages;

use App\Settings\AppSettings;
use Filament\Forms;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class ManageApp extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static ?string $navigationLabel = 'Paramètres';

    protected ?string $heading = 'Paramètres du Système';

    protected static ?string $title = 'Paramètres du Système';

    protected static ?string $navigationGroup = 'Configuration';

    protected static ?int $navigationSort = 100;

    protected static string $settings = AppSettings::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Paramètres')
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('Général')
                            ->schema([
                                Section::make('Informations du Système')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Nom du Système')
                                            ->placeholder('Veuillez entrer le nom du système')
                                            ->required()
                                            ->validationMessages([
                                                'required' => 'Le nom du système est requis.',
                                                'max' => 'Le nom du système ne peut pas dépasser 255 caractères.',
                                            ])
                                            ->maxLength(255),
                                        Textarea::make('description')
                                            ->label('Description du Système')
                                            ->placeholder('Veuillez entrer une description du système')
                                            ->validationMessages([
                                                'max' => 'La description ne peut pas dépasser 1000 caractères.',
                                            ])
                                            ->maxLength(1000),
                                        FileUpload::make('logo')
                                            ->image()
                                            ->directory('site')
                                            ->visibility('public'),
                                    ]),

                                Section::make('Informations de Contact')
                                    ->schema([
                                        TextInput::make('email')
                                            ->email()
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('phone')
                                            ->tel()
                                            ->required()
                                            ->maxLength(20),
                                        Section::make('Addresse')
                                            ->columns(2)
                                            ->schema([
                                                Textarea::make('address')
                                                    ->hiddenLabel()
                                                    ->required()
                                                    ->maxLength(500),
                                            ])->columnSpanFull(),
                                    ])->columns(2),
                            ]),

                        Tabs\Tab::make('Horaires de Travail')
                            ->hidden()
                            ->schema([
                                Section::make('Configuration des Horaires de Travail')
                                    ->columns(2)
                                    ->schema([

                                        Forms\Components\Select::make('working_days_per_week')
                                            ->label('Jours ouvrables par semaine')
                                            ->options([
                                                5 => '5 (Du Lundi au Vendredi)',
                                                6 => '6 (Du Lundi au Samedi)',
                                            ])
                                            ->helperText('Le nombre de jours ouvrables par semaine')
                                            ->required(),

                                        Forms\Components\Select::make('working_hours_per_day')
                                            ->label('Heures de travail par jour')
                                            ->options(array_combine(range(5, 12), array_map(fn ($i) => "{$i}h", range(5, 12))))
                                            ->helperText("Le nombre d'heures de travail par jour")
                                            ->required(),
                                    ]),

                            ]),
                    ]),
            ]);
    }

    public function currentlyValidatingForm(?ComponentContainer $form): void
    {
        // This method is required by the interface but not used in this implementation
    }
}
