<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    public static function getModelLabel(): string
    {
        return 'Utilisateur';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Utilisateurs';
    }

    public static function getNavigationBadge(): ?string
    {
        if (auth()->user()->hasRole('super_admin') || auth()->user()->can('view_all_user')) {
            return static::getModel()::count();
        } else {
            return static::getModel()::where('created_by', auth()->id())->count();
        }
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where(function ($query) {

            if (auth()->user()?->hasRole('super_admin')
                || auth()->user()?->can('view_all_user')
            ) {
                return $query;
            }

            return $query->where('created_by', auth()->id());
        });
    }

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Gestion du Panneaux';

    protected static ?int $navigationSort = 3000;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make("Informations d'Utilisateur")
                    ->disabled(! auth()->user()->hasRole('super_admin'))
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->relationship('roles', 'name')
                            ->placeholder('Sélectionner un rôle')
                            // ->multiple()
                            ->preload()
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->label('Nom et Prénom')
                            ->placeholder('Nom et Prénom')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->placeholder('Email')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->required(),
                        Forms\Components\TextInput::make('phone')
                            ->label('Téléphone')
                            ->placeholder('Téléphone')
                            ->minLength(8)
                            ->maxLength(8)
                            ->numeric()
                            ->unique(
                                'users',
                                'phone',
                                ignoreRecord: true,
                                modifyRuleUsing: function ($rule) {
                                    return $rule->whereNull('deleted_at');
                                }
                            )
                            ->validationMessages([
                                'phone.unique' => 'Ce numéro de téléphone existe déjà.',
                                'required' => 'Le téléphone est requis.',
                                'min' => 'Le téléphone doit contenir au moins 8 chiffres.',
                                'max' => 'Le téléphone doit contenir au plus 8 chiffres.',
                                'numeric' => 'Le téléphone doit être un nombre.',
                            ]),
                        Forms\Components\Toggle::make('status')
                            ->hiddenOn('create')
                            ->visible(fn ($record) => $record->isNot(auth()->user()) && ! $record->roles->contains('name', 'super_admin'))
                            ->label('Statut')
                            ->helperText('Activer ou désactiver l\'utilisateur.')
                            ->onColor('success')
                            ->offColor('warning')
                            ->default(true)
                            ->columnSpanFull(),
                        Forms\Components\Fieldset::make('Mot de Passe')
                            ->schema([
                                Forms\Components\TextInput::make('password')
                                    ->label('Mot de passe')
                                    ->placeholder('Mot de passe')
                                    ->password()
                                    ->minLength(8)
                                    ->validationMessages([
                                        'password' => 'Le mot de passe doit contenir au moins 8 caractères.',
                                        'required' => 'Le mot de passe est requis.',
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('password_confirmation')
                                    ->label('Confirmer le mot de passe')
                                    ->placeholder('Confirmer le mot de passe')
                                    ->password()
                                    ->same('password')
                                    ->minLength(8)
                                    ->validationMessages([
                                        'password_confirmation' => 'Les mots de passe ne correspondent pas.',
                                        'required' => 'La confirmation du mot de passe est requise.',
                                        'same' => 'Les mots de passe ne correspondent pas.',
                                    ])
                                    ->required(),
                            ])->visibleOn(['create']),
                    ])->columns(2),

                Forms\Components\Section::make()
                    ->visible(fn ($record) => $record->is(auth()->user()) || $record->roles->contains('name', 'super_admin'))
                    ->visibleOn(['edit'])
                    ->schema([
                        Forms\Components\Fieldset::make('Créez un Nouveau Mot de Passe')
                            ->schema([
                                Forms\Components\TextInput::make('new_password')
                                    ->label('Nouveau mot de passe')
                                    ->placeholder('Nouveau mot de passe')
                                    ->password()
                                    ->minLength(8)
                                    ->nullable(),
                                Forms\Components\TextInput::make('new_password_confirmation')
                                    ->label('Confirmer le mot de passe')
                                    ->password()
                                    ->same('new_password')
                                    ->minLength(8)
                                    ->validationMessages([
                                        'new_password_confirmation' => 'Les mots de passe ne correspondent pas.',
                                        'required_with' => 'La confirmation du mot de passe est requise.',
                                        'same' => 'Les mots de passe ne correspondent pas.',
                                    ])
                                    ->requiredWith('new_password'),
                            ]),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom et Prénom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Rôles')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => Str::headline($state))
                    ->color('primary')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Créé par')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->searchable()
                    ->sortable()
                    ->date('d/m/Y H:i')
                    ->alignEnd(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->hidden(fn ($record) => $record->is(auth()->user()) || $record->roles->contains('name', 'super_admin')),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            // 'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
