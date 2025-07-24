<?php

namespace App\Filament\Resources;

use App\Enums\GenderEnum;
use App\Filament\Resources\GuestResource\Pages;
use App\Models\Guest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GuestResource extends Resource
{
    protected static ?string $model = Guest::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Gestion des invités';

    protected static ?int $navigationSort = 30;

    public static function shouldRegisterNavigation(): bool
    {
        return config('module.guests.enable');
    }

    public static function getLabel(): ?string
    {
        return 'Invité';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Invités';
    }

    public static function getNavigationBadge(): ?string
    {
        if (auth()->user()->hasRole('super_admin') || auth()->user()->can('view_all_guest')) {
            return static::getModel()::count();
        } else {
            return static::getModel()::where('created_by', auth()->id())->count();
        }
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where(function ($query) {

            if (auth()->user()?->hasRole('super_admin') || auth()->user()?->can('view_all_guest')
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
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Fieldset::make('Informations Générales')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Titre')
                                    ->placeholder('Titre de l\'invité')
                                    ->validationMessages([
                                        'required' => 'Le titre est obligatoire.',
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('name')
                                    ->label('Nom complet')
                                    ->placeholder('Nom complet de l\'invité')
                                    ->validationMessages([
                                        'required' => 'Le nom complet est obligatoire.',
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('city')
                                    ->label('Ville')
                                    ->placeholder('Ville de l\'invité'),
                                Forms\Components\Select::make('gender')
                                    ->label('Genre')
                                    ->options(GenderEnum::class)
                                    ->placeholder('Sélectionner un genre')
                                    ->validationMessages([
                                        'required' => 'Le genre est obligatoire.',
                                    ])
                                    ->required(),
                                Forms\Components\Fieldset::make('Contact')
                                    ->schema([
                                        Forms\Components\TextInput::make('phone')
                                            ->label('Téléphone')
                                            ->placeholder('Numéro de téléphone de l\'invité')
                                            ->numeric()
                                            ->unique(
                                                'guests',
                                                'phone',
                                                ignoreRecord: true,
                                                modifyRuleUsing: function ($rule) {
                                                    return $rule->whereNull('deleted_at');
                                                }
                                            )
                                            ->minLength(8)
                                            ->maxLength(11)
                                            ->validationMessages([
                                                'required' => 'Le téléphone est obligatoire.',
                                                'phone.unique' => 'Le téléphone est déjà utilisé.',
                                                'phone.numeric' => 'Le téléphone doit être un nombre.',
                                                'phone.min' => 'Le téléphone doit être au moins :min chiffres.',
                                                'phone.max' => 'Le téléphone doit être au plus :max chiffres.',
                                            ])
                                            ->required(),
                                        Forms\Components\TextInput::make('email')
                                            ->label('Email')
                                            ->placeholder('Adresse email de l\'invité')
                                            ->email()
                                            ->unique(
                                                'guests',
                                                'email',
                                                ignoreRecord: true,
                                                modifyRuleUsing: function ($rule) {
                                                    return $rule->whereNull('deleted_at');
                                                }
                                            )
                                            ->validationMessages([
                                                'email' => 'L\'email doit être une adresse email valide.',
                                                'email.unique' => 'L\'email est déjà utilisé.',
                                            ]),
                                        Forms\Components\Fieldset::make('Adresse')
                                            ->label('Addresse de l\'invité')
                                            ->schema([
                                                Forms\Components\Textarea::make('address')
                                                    ->hiddenLabel()
                                                    ->placeholder('Adresse de l\'invité'),
                                            ])->columns(1),
                                    ]),
                                Forms\Components\Fieldset::make('Documents de l\'Invité')
                                    ->schema([
                                        Forms\Components\SpatieMediaLibraryFileUpload::make('guest_documents')
                                            ->hiddenLabel()
                                            ->collection('guest_documents')
                                            ->multiple()
                                            ->downloadable()
                                            ->openable()
                                            ->maxSize(10240)
                                            ->maxFiles(150)
                                            ->helperText('Le fichier doit être en format PDF ou image et ne doit pas dépasser 10 Mo')
                                            ->acceptedFileTypes(
                                                [
                                                    'application/pdf',
                                                    'image/*',
                                                ]
                                            )
                                            ->maxFiles(150)
                                            ->validationMessages([
                                                'max_size' => 'Le fichier ne doit pas dépasser :max Ko.',
                                                'max_files' => 'Le nombre de fichiers ne doit pas dépasser :max.',
                                                'accepted_file_types' => 'Le fichier doit être en format PDF ou image.',
                                            ]),
                                    ])->columns(1),
                            ]),
                        Forms\Components\Fieldset::make('Institution')
                            ->schema([
                                Forms\Components\TextInput::make('company')
                                    ->label("Nom de l'Institution")
                                    ->placeholder('Nom de l\'institution de l\'invité'),
                                Forms\Components\TextInput::make('company_phone')
                                    ->label('Téléphone de l\'Institution')
                                    ->placeholder('Numéro de téléphone de l\'institution de l\'invité')
                                    ->numeric()
                                    ->minLength(8)
                                    ->maxLength(11)
                                    ->validationMessages([
                                        'phone.numeric' => 'Le téléphone doit être un nombre.',
                                        'phone.min' => 'Le téléphone doit être au moins :min chiffres.',
                                        'phone.max' => 'Le téléphone doit être au plus :max chiffres.',
                                    ]),
                                Forms\Components\Fieldset::make('Adresse de l\'Institution')
                                    ->schema([
                                        Forms\Components\Textarea::make('company_address')
                                            ->hiddenLabel()
                                            ->placeholder('Adresse de l\'institution de l\'invité'),
                                    ])->columns(1),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom complet')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gender')
                    ->label('Genre')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('programmed')
                    ->label('Programmé')
                    ->getStateUsing(fn ($record) => $record->guestItems()->exists() ? 'Oui' : 'Non')
                    ->badge()
                    ->color(fn ($record) => $record->guestItems()->exists() ? 'success' : 'warning')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Créé par')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updatedBy.name')
                    ->label('Modifié par')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->getStateUsing(fn ($record) => $record->updated_by ? $record->updated_at : null)
                    ->label('Modifié le')
                    ->date('d/m/Y')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->hidden(fn ($record) => $record->guestItems()->exists()),
                ]),
            ])
            ->bulkActions([
                //
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
            'index' => Pages\ListGuests::route('/'),
            'create' => Pages\CreateGuest::route('/create'),
            'edit' => Pages\EditGuest::route('/{record}/edit'),
            'view' => Pages\ViewGuest::route('/{record}'),
        ];
    }
}
