<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Enums\EmployeeStatusEnum;
use App\Filament\Resources\EmployeeResource;
use App\Models\Employee;
use App\Models\Task;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(fn ($record) => $record->employeeProductItems()->exists() || $record->internItems()->exists() || $record->employeePositions()->exists() || $record->leaves()->exists() || $record->debts()->exists() || $record->employeeLeaveBalances()->exists() || $record->trashed()),
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

        //        $newTask = Task::find($data['task_id']);
        //        $oldTask = Task::find($this->record->task_id);
        //
        //        // Eğer görev değiştiyse eski ve yeni görev durumlarını güncelle
        //        if ($data['task_id'] != $this->record->task_id) {
        //            // Yeni görevi "meşgul" yap
        //            if ($newTask) {
        //                $newTask->update([
        //                    'is_available' => 0,
        //                    'updated_by' => auth()->id(),
        //                ]);
        //            }
        //
        //            // Kayıt güncellemesi
        //            $this->record->update([
        //                'task_id' => $data['task_id'],
        //                'updated_by' => auth()->id(),
        //            ]);
        //        }
        //
        //        // Eski görevi kontrol et ve boşsa "aktif" yap
        //        if ($oldTask) {
        //            $employeeExists = Employee::where('task_id', $oldTask->id)->exists();
        //
        //            if (! $employeeExists) {
        //                $oldTask->update([
        //                    'is_available' => 1,
        //                    'updated_by' => auth()->id(),
        //                ]);
        //            }
        //        }

        //        $dataStatus = $data['status'];
        //
        //        if (isset($data['end_date']) and now()->isAfter($data['end_date'])) {
        //            $data['status'] = EmployeeStatusEnum::CONTRACT_ENDED->value;
        //        } else {
        //            $data['status'] = $dataStatus;
        //        }

        return $data;
    }
}
