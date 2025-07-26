<?php

namespace App\Filament\Clusters\Enter\Resources\InvoiceResource\Pages;

use App\Filament\Clusters\Enter\Resources\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    //protected ?string $heading = 'Créer une Nouvelle Facture';

    protected static string $resource = InvoiceResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', [
            'record' => $this->record,
        ]);
    }

    public function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
