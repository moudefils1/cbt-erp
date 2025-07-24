<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(fn ($record) => $record->is(auth()->user())),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    public function mutateFormDataBeforeSave(array $data): array
    {
        if (filled($data['new_password'])) {
            $this->record->password = $data['new_password'];
        }

        $data['updated_by'] = auth()->id();

        return $data;
    }
}
