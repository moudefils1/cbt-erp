<?php

namespace App\Filament\Resources\TrainingResource\Pages;

use App\Filament\Resources\TrainingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTraining extends ViewRecord
{
    protected static string $resource = TrainingResource::class;

    protected ?string $heading = 'Détails de la Formation';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make()
                ->hidden(fn ($record) => $record->employeeTrainings()->exists()),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            'employeeTrainings' => TrainingResource\RelationManagers\EmployeeTrainingsRelationManager::class,
        ];
    }
}
