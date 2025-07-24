<?php

namespace App\Filament\Resources\GuestResource\RelationManagers;

use App\Enums\ApprovalEnum;
use App\Enums\EmployeeStatusEnum;
use App\Enums\StateEnum;
use App\Enums\StatusEnum;
use App\Models\Employee;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class GuestItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'guestItems';

    protected static ?string $title = 'Planning de l\'Invité';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->label('Journaliste')
                    ->placeholder('Sélectionner un journaliste')
                    ->options(fn () => Employee::where('status', EmployeeStatusEnum::WORKING)
                        ->get()
                        ->pluck('full_name', 'id')
                        ->toArray()
                    )
                    ->validationMessages([
                        'required' => 'Le responsable de l\'invité est obligatoire.',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('subject')
                    ->label('Sujet')
                    ->placeholder("Sujet de l'invité")
                    ->validationMessages([
                        'required' => 'Le sujet est obligatoire.',
                    ])
                    ->required(),
                Forms\Components\Fieldset::make('Dates')
                    ->schema([
                        Forms\Components\DateTimePicker::make('start_date')
                            ->label('Date de début')
                            ->default(now()->format('Y-m-d H:i'))
                            ->minDate($this->ownerRecord->created_at ?? now()->format('Y-m-d H:i'))
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('end_date', null))
                            ->placeholder('Sélectionner une date')
                            ->validationMessages([
                                'required' => 'La date de début est obligatoire.',
                            ])
                            ->required(),
                        Forms\Components\DateTimePicker::make('end_date')
                            ->label('Date de fin')
                            ->placeholder('Sélectionner une date')
                            ->after('start_date')
                            ->minDate(fn ($get) => $get('start_date'))
                            ->validationMessages([
                                'required' => 'La date de fin est obligatoire.',
                                'after' => 'La date de fin doit être postérieure à la date de début.',
                            ])
                            ->required(),
                        Forms\Components\Fieldset::make('Statut')
                            ->visibleOn(['edit', 'view'])
                            ->schema([
                                Forms\Components\Select::make('approval')
                                    ->hiddenLabel()
                                    ->placeholder('Sélectionner')
                                    ->options(ApprovalEnum::class)
                                    ->validationMessages([
                                        'required' => 'Champ obligatoire.',
                                    ])
                                    ->live()
                                    ->columnSpanFull()
                                    ->required(),
                                Forms\Components\Grid::make()
                                    ->visible(fn ($get) => $get('approval') == ApprovalEnum::POSTPONED->value)
                                    ->schema([
                                        self::getPostponedReason(),
                                    ]),
                                Forms\Components\Grid::make()
                                    ->visible(fn ($get) => $get('approval') == ApprovalEnum::CANCELED->value)
                                    ->schema([
                                        self::getCancelReason(),
                                    ]),
                            ]),
                    ]),
                Forms\Components\Fieldset::make('Documents relatifs au planning')
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('guest_items_documents')
                            ->hiddenLabel()
                            ->collection('guest_items_documents')
                            ->multiple()
                            ->downloadable()
                            ->openable()
                            ->maxSize(10240)
                            ->maxFiles(150)
                            ->helperText('Le fichier doit être en format PDF ou image et ne doit pas dépasser 10 Mo.')
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
                Forms\Components\Fieldset::make('Résumé')
                    ->visibleOn(['view'])
                    ->visible(fn ($get) => $get('state') == StateEnum::COMPLETED->value && $get('approval') == ApprovalEnum::APPROVED->value)
                    ->schema([
                        Forms\Components\Textarea::make('resume')
                            ->hiddenLabel()
                            ->placeholder('Résumé de l\'invité')
                            ->validationMessages([
                                'required' => 'Le résumé est obligatoire.',
                            ]),
                    ])->columns(1),
            ]);
    }

    private static function getPostponedReason(): Forms\Components\Fieldset
    {
        return Forms\Components\Fieldset::make('Report')
            ->schema([
                Forms\Components\DateTimePicker::make('postponed_at')
                    ->label('Date du report')
                    ->placeholder('Date du report')
                    ->after('end_date')
                    ->validationMessages([
                        'required' => 'La date du report est obligatoire.',
                        'after' => fn ($record) => 'La date du report doit être postérieure à la date de : '.($record->end_date ? Carbon::parse($record->end_date)->format('d/m/Y H:i') : 'la date de fin'),
                    ])
                    ->required(),
                Forms\Components\Textarea::make('postponed_reason')
                    ->label('Raison du report')
                    ->placeholder('Raison du report')
                    ->validationMessages([
                        'required' => 'La raison du report est obligatoire.',
                    ])
                    ->required(),
            ])->columns(1);
    }

    private static function getCancelReason(): Forms\Components\Fieldset
    {
        return Forms\Components\Fieldset::make('Annulation')
            ->schema([
                Forms\Components\Textarea::make('cancel_reason')
                    ->label('Raison de l\'annulation')
                    ->placeholder('Raison de l\'annulation')
                    ->validationMessages([
                        'required' => 'La raison de l\'annulation est obligatoire.',
                    ])
                    ->required(),
            ])->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Journaliste'),
                Tables\Columns\TextColumn::make('subject')
                    ->label('Sujet'),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Date de Début')
                    ->badge()
                    ->color(fn ($record) => match ($record->state) {
                        StateEnum::COMPLETED => 'success',
                        StateEnum::IN_PROGRESS => 'info',
                        StateEnum::STANDBY => 'warning',
                        default => 'gray',
                    })
                    ->date('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Date de Fin')
                    ->badge()
                    ->color(fn ($record) => match ($record->state) {
                        StateEnum::COMPLETED => 'success',
                        default => 'gray',
                    })
                    ->date('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('approval')
                    ->label('Statut')
                    ->badge(),
                Tables\Columns\TextColumn::make('postponed_at')
                    ->label('Date du Report')
                    ->badge()
                    ->color('warning')
                    ->date('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('canceled_at')
                    ->label('Date de l\'Annulation')
                    ->badge()
                    ->color('danger')
                    ->date('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('state')
                    ->label('Etat Actuel')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->hidden($this->ownerRecord->status == StatusEnum::INACTIVE)
                    ->modalHeading('Ajouter un planning de l\'invité')
                    ->mutateFormDataUsing(function ($data) {
                        $data['created_by'] = auth()->id();

                        if ($data['start_date'] > now()->format('Y-m-d H:i')) {
                            $data['state'] = StateEnum::STANDBY;
                        } elseif ($data['start_date'] < now()->format('Y-m-d H:i') && $data['end_date'] > now()->format('Y-m-d H:i')) {
                            $data['state'] = StateEnum::IN_PROGRESS;
                        } elseif ($data['start_date'] < now()->format('Y-m-d H:i') && $data['end_date'] == now()->format('Y-m-d H:i')) {
                            $data['state'] = StateEnum::IN_PROGRESS;
                        } elseif ($data['start_date'] == now()->format('Y-m-d H:i') && $data['end_date'] > now()->format('Y-m-d H:i')) {
                            $data['state'] = StateEnum::IN_PROGRESS;
                        } else {
                            $data['state'] = StateEnum::COMPLETED;
                        }

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->modalHeading('Modifier le planning de l\'invité')
                        ->mutateFormDataUsing(function ($data) {

                            $data['updated_by'] = auth()->id();

                            if (isset($data['approval'])) {

                                $postponedReason = $data['postponed_reason'] ?? null;
                                $postponedAt = $data['postponed_at'] ?? null;
                                $cancelReason = $data['cancel_reason'] ?? null;

                                if ($data['start_date'] > now()->format('Y-m-d H:i')) {
                                    $data['state'] = StateEnum::STANDBY;
                                } elseif ($data['start_date'] < now()->format('Y-m-d H:i') && $data['end_date'] > now()->format('Y-m-d H:i')) {
                                    $data['state'] = StateEnum::IN_PROGRESS;
                                } elseif ($data['start_date'] < now()->format('Y-m-d H:i') && $data['end_date'] == now()->format('Y-m-d H:i')) {
                                    $data['state'] = StateEnum::IN_PROGRESS;
                                } elseif ($data['start_date'] == now()->format('Y-m-d H:i') && $data['end_date'] > now()->format('Y-m-d H:i')) {
                                    $data['state'] = StateEnum::IN_PROGRESS;
                                } else {
                                    $data['state'] = StateEnum::COMPLETED;
                                }

                                if ($data['approval'] == ApprovalEnum::POSTPONED->value) {
                                    $data['postponed_by'] = auth()->id();
                                    $data['postponed_at'] = $postponedAt;
                                    $data['postponed_reason'] = $postponedReason;
                                    $data['canceled_at'] = null;
                                    $data['state'] = StateEnum::STANDBY;
                                } elseif ($data['approval'] == ApprovalEnum::CANCELED->value) {
                                    $data['canceled_by'] = auth()->id();
                                    $data['canceled_at'] = now();
                                    $data['cancel_reason'] = $cancelReason;
                                    $data['postponed_at'] = null;
                                    $data['state'] = StateEnum::COMPLETED;
                                }

                                //                                if ($data['approval'] == ApprovalEnum::CANCELED->value) {
                                //                                    $data['state'] = StateEnum::CANCELED;
                                //                                }
                            }

                            return $data;
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->modalHeading('Supprimer le planning de l\'invité'),
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
}
