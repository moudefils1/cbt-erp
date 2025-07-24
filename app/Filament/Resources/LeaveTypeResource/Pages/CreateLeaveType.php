<?php

namespace App\Filament\Resources\LeaveTypeResource\Pages;

use App\Filament\Resources\LeaveTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLeaveType extends CreateRecord
{
    protected static string $resource = LeaveTypeResource::class;

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
