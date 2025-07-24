<?php

namespace App\Filament\Clusters\Enter\Resources\SupplierResource\Pages;

use App\Filament\Clusters\Enter\Resources\SupplierResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSupplier extends CreateRecord
{
    protected static string $resource = SupplierResource::class;

    // public static ?string $title = 'Ajouter un Fournisseur';

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    public function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
