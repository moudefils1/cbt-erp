<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Enums\EmployeeStatusEnum;
use App\Filament\Resources\EmployeeResource;
use App\Models\Task;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', [
            'record' => $this->record,
        ]);
    }

    public function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        // If the task is assigned to the employee, then the task should be unavailable
        //        if (isset($data['task_id'])) {
        //            Task::where('id', $data['task_id'])
        //                ->update([
        //                    'is_available' => 0,
        //                    'updated_by' => auth()->id(),
        //                ]);
        //        }

        //        $dataStatus = $data['status'];
        //
        //        if (isset($data['end_date']) and now()->isAfter($data['end_date'])) {
        //            $data['status'] = EmployeeStatusEnum::CONTRACT_ENDED->value;
        //            $data['status_start_date'] = $data['end_date'];
        //        } else {
        //            $data['status'] = $dataStatus;
        //        }

        return $data;
    }
}
