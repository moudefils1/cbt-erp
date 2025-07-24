<?php

namespace App\Filament\Resources\InternResource\Pages;

use App\Enums\StateEnum;
use App\Filament\Resources\InternResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIntern extends CreateRecord
{
    protected static string $resource = InternResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', [
            'record' => $this->record,
        ]);
    }

    public function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['internship_duration'] = date_diff(date_create($data['internship_start_date']), date_create($data['internship_end_date']))->format('%a');

        //        if ($data['internship_end_date'] < now()->format('Y-m-d')) {
        //            $data['status'] = StateEnum::COMPLETED;
        //        } else if ($data['internship_end_date'] > now()->format('Y-m-d')) {
        //            $data['status'] = StateEnum::STANDBY;
        //        } else {
        //            $data['status'] = StateEnum::IN_PROGRESS;
        //        }

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
