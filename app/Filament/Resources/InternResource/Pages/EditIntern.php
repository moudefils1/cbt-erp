<?php

namespace App\Filament\Resources\InternResource\Pages;

use App\Enums\StateEnum;
use App\Filament\Resources\InternResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIntern extends EditRecord
{
    protected static string $resource = InternResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(fn ($record) => $record->status->value == StateEnum::COMPLETED->value || $record->internItems()->exists()),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', [
            'record' => $this->record,
        ]);
    }

    public function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        $data['internship_duration'] = date_diff(date_create($data['internship_start_date']), date_create($data['internship_end_date']))->format('%a');

        if ($data['internship_start_date'] > now()->format('Y-m-d')) {
            $data['status'] = StateEnum::STANDBY;
        } elseif ($data['internship_start_date'] < now()->format('Y-m-d') && $data['internship_end_date'] > now()->format('Y-m-d')) {
            $data['status'] = StateEnum::IN_PROGRESS;
        } elseif ($data['internship_start_date'] < now()->format('Y-m-d') && $data['internship_end_date'] == now()->format('Y-m-d')) {
            $data['status'] = StateEnum::IN_PROGRESS;
        } elseif ($data['internship_start_date'] == now()->format('Y-m-d') && $data['internship_end_date'] > now()->format('Y-m-d')) {
            $data['status'] = StateEnum::IN_PROGRESS;
        } else {
            $data['status'] = StateEnum::COMPLETED;
        }

        return $data;
    }
}
