<?php

namespace App\Filament\Clusters\Enter\Resources\InvoiceResource\Pages;

use App\Filament\Clusters\Enter\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected ?string $heading = 'Détails de la Fourniture';

    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make()
                ->hidden(fn ($record) => $record->products()->exists()),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            InvoiceResource\RelationManagers\ProductsRelationManager::class,
            InvoiceResource\RelationManagers\PaymentsRelationManager::class,
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Card::make()
                    ->schema([
                        Infolists\Components\TextEntry::make('invoice_number')
                            ->label('Numéro de la Facture')
                            ->badge()
                            ->color('warning'),
                        Infolists\Components\TextEntry::make('amount')
                            ->label('Montant')
                            ->badge()
                            ->color('primary')
                            ->formatStateUsing(fn ($state) => number_format($state, 2) . ' CFA'),
                        Infolists\Components\TextEntry::make('supplier.enterprise_name')
                            ->label('Fournisseur'),
                        Infolists\Components\TextEntry::make('invoice_status')
                            ->label('Etat de la Fourtniture')
                            ->badge()
                            ->color(fn ($record) => match ($record->invoice_status->value) {
                                0 => 'warning',
                                1 => 'success',
                            }),
                        Infolists\Components\Fieldset::make('Receptionnaire(s)')
                            ->schema([
                                Infolists\Components\TextEntry::make('receptionist_names') // Accessor kullanılıyor (Invoice içinde tanımlıdır)
                                ->hiddenLabel()
                                    ->badge()
                                    ->color('info')
                                    ->formatStateUsing(function ($state) {
                                        // İsimleri virgülle ayır
                                        return is_array($state) ? implode(', ', $state) : $state;
                                    }),
                            ])->columns(2),
                    ])->columns(3),
                Infolists\Components\Card::make()
                    ->schema([
                        Infolists\Components\Fieldset::make('Dates')
                            ->schema([
                                Infolists\Components\TextEntry::make('createdBy.full_name')
                                    ->label('Créé par')
                                    ->badge()
                                    ->color('primary'),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Créé le')
                                    ->badge()
                                    ->color('primary')
                                    ->date('d/m/Y H:i'),
                                Infolists\Components\TextEntry::make('updatedBy.name')
                                    ->label('Modifié par')
                                    ->badge()
                                    ->color('success')
                                    ->hidden(fn ($record) => $record->updatedBy == null),
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Modifié le')
                                    ->badge()
                                    ->color('success')
                                    ->date('d/m/Y H:i')
                                    ->hidden(fn ($record) => $record->updated_by == null),
                            ])->columns(4),
                    ])->columnSpanFull(),

                Infolists\Components\Card::make()
                    ->hidden(fn ($record) => $record->invoice_description == null)
                    ->schema([
                        Infolists\Components\TextEntry::make('invoice_description')
                            ->label('Description'),
                    ])->columnSpanFull(),
            ]);
    }
}
