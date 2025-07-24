<?php

namespace App\Filament\Resources\InternItemResource\Pages;

use App\Filament\Resources\InternItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInternItem extends EditRecord
{
    protected static string $resource = InternItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
