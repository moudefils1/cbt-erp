<?php

namespace App\Filament\Resources\DebtResource\Pages;

use App\Filament\Resources\DebtResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDebt extends CreateRecord
{
    protected static string $resource = DebtResource::class;

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

    //    protected function afterCreate(): void
    //    {
    //        // Create a debt item with the full amount as remaining
    //        $this->record->items()->create([
    //            'amount' => $this->record->amount,
    //            'remaining_amount' => $this->record->amount,
    //            'paid_amount' => 0,
    //            'created_by' => auth()->id(),
    //        ]);
    //    }
}
