<?php

namespace App\Filament\Resources\PayrollDesignationResource\Pages;

use App\Filament\Resources\PayrollDesignationResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayrollDesignation extends CreateRecord
{
    protected static string $resource = PayrollDesignationResource::class;

    //    protected function mutateFormDataBeforeCreate(array $data): array
    //    {
    //        // If there are no designations, just return the original data
    //        if (empty($data['designations'] ?? [])) {
    //            return $data;
    //        }
    //
    //        // Get the first designation to use for the main record
    //        $firstDesignation = $data['designations'][0];
    //
    //        // Store other designations for afterCreate hook
    //        $this->otherDesignations = array_slice($data['designations'], 1);
    //
    //        // Return the transformed data for the first record
    //        return [
    //            'name' => $firstDesignation['name'],
    //            'part' => $firstDesignation['part'],
    //            'operation' => $firstDesignation['operation'],
    //            'created_by' => $firstDesignation['created_by'] ?? auth()->id(),
    //        ];
    //    }
    //
    //    protected function afterCreate(): void
    //    {
    //        // Create the additional designations
    //        if (!empty($this->otherDesignations)) {
    //            foreach ($this->otherDesignations as $designation) {
    //                $this->getModel()::create([
    //                    'name' => $designation['name'],
    //                    'part' => $designation['part'],
    //                    'operation' => $designation['operation'],
    //                    'created_by' => $designation['created_by'] ?? auth()->id(),
    //                ]);
    //            }
    //        }
    //    }
}
