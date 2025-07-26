<?php

namespace App\Filament\Clusters\Enter\Resources\SupplierResource\RelationManagers;

use App\Filament\Clusters\Enter\Resources\InvoiceResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    protected static ?string $title = 'Fournitures';

    protected static ?string $label = 'Fourniture';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de la Facture')
                    ->description('Veuillez renseigner les informations de la facture.')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make()
                                    // ->description("Veuillez renseigner les informations du fournisseur")
                                    // ->collapsible()
                                    ->schema([
                                        Forms\Components\TextInput::make('invoice_number')
                                            ->label('Facture No')
                                            ->placeholder('Saisissez le numéro de la facture')
                                            ->unique(ignoreRecord: true)
                                            ->validationMessages([
                                                'required' => 'Veuillez saisir le numéro de la facture',
                                                'max' => 'Le numéro de la facture ne doit pas dépasser :max caractères',
                                                'unique' => 'Cette facture existe déjà',
                                            ])
                                            ->required(),

                                        Forms\Components\SpatieMediaLibraryFileUpload::make('invoice_doc')
                                            ->columnSpanFull()
                                            ->label('Document de la Facture')
                                            ->collection('invoice_doc')
                                            ->acceptedFileTypes(['application/pdf'])
                                            ->downloadable()
                                            ->openable()
                                            ->multiple()
                                        /*->validationMessages([
                                            'required' => 'Veuillez télécharger la ou les facture(s) du bon d\'entrée',
                                        ])
                                        ->required()*/,
                                    ]),
                            ]),

                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\Select::make('receptionists')
                                            ->label('Receptionnaire(s)')
                                            ->options(
                                                \App\Models\Employee::all()->pluck('full_name', 'id')
                                            )
                                            ->placeholder('Sélectionnez le(s) receptionnaire(s)')
                                            ->multiple()
                                            ->preload()
                                            ->validationMessages([
                                                'required' => 'Veuillez sélectionner le(s) receptionnaire(s)',
                                            ])
                                            ->required(),
                                        Forms\Components\SpatieMediaLibraryFileUpload::make('receipt_doc')
                                            ->columnSpanFull()
                                            ->label('Fiche de Réception du Produit')
                                            ->collection('receipt_doc')
                                            ->acceptedFileTypes(['application/pdf'])
                                            ->downloadable()
                                            ->openable()
                                            ->multiple(),
                                    ])->columns(1),
                            ]),
                        Forms\Components\Toggle::make('invoice_status')
                            ->label('Etat du Bon d\'Entrée (Definif ou Provisoire)')
                            ->helperText('Vous devez uniquement desactiver cette option si le bon d\'entrée est provisoire.')
                            ->onColor('success')
                            ->offColor('warning')
                            ->default(true)
                            ->columnSpanFull(),
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('invoice_description')
                                    ->label('Description de la Facture')
                                    ->placeholder('Faite une description de la facture')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('invoice_number')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Facture No')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('products_count')
                    ->label('Produits')
                    ->counts('products')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice_status')
                    ->label('Etat de la Fourniture')
                    ->badge()
                    ->color(fn ($record) => match ($record->invoice_status->value) {
                        0 => 'warning',
                        1 => 'success',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->visible(fn () => auth()->user()->hasRole('super_admin'))
                    ->label('Créé par')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->date('d/m/Y')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Dernière Modification')
                    ->date('d/m/Y')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // on ne peut ajouter une facture sans des fournitures
                Tables\Actions\CreateAction::make()
                    ->url(fn () => InvoiceResource::getUrl('create', ['record' => $this->ownerRecord]))
                    ->visible(fn () => $this->ownerRecord->is_active),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record) => InvoiceResource::getUrl('view', ['record' => $record])),
                Tables\Actions\EditAction::make()
                    ->hidden(fn () => $this->shouldHideAction()),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn ($record) => $record->products()->exists() || $this->shouldHideAction()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // utilisé pour afficher ou cacher les buttons d'actions
    protected function shouldHideAction(): bool
    {
        $supplierId = $this->ownerRecord->getKey(); // id du fournisseur

        // si le fournisseur n'existe pas ou est inactif on cache les actions
        return ! $supplierId || \App\Models\Supplier::where('id', $supplierId)->where('is_active', 0)->exists();
    }
}
