<?php

namespace App\Filament\Exports;

use App\Enums\EmployeeStatusEnum;
use App\Enums\EmployeeTypeEnum;
use App\Models\Employee;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class EmployeeExporter extends Exporter
{
    protected static ?string $model = Employee::class;

    public static function getColumns(): array
    {
        return [
            /*ExportColumn::make('id')
                ->label('ID'),*/
            ExportColumn::make('name')
                ->label('Nom'),
            ExportColumn::make('surname')
                ->label('Prénom'),
            ExportColumn::make('matricule')
                ->label('Matricule'),
            ExportColumn::make('nni')
                ->label('NNI')
            /* ->enabledByDefault(false) */,
            ExportColumn::make('cnps_no')
                ->label('Numéro de CNPS'),
            ExportColumn::make('phone')
                ->label('Téléphone'),
            ExportColumn::make('email')
                ->label('Email'),
            ExportColumn::make('location.name')
                ->label('Département'),
            ExportColumn::make('task.name')
                ->label('Poste'),
            ExportColumn::make('status')
                ->label('Statut')
                ->formatStateUsing(fn (EmployeeStatusEnum $state) => $state->getLabel()),
            /*ExportColumn::make('employee_type_id')
                ->label("Type de Personnel")
                ->formatStateUsing(fn (EmployeeTypeEnum $type) => $type->getLabel())
                ->limit(fn (array $options): int => $options['employee_type_id'] ?? 100),*/
            ExportColumn::make('createdBy.name')
                ->label('Créé par'),
            ExportColumn::make('created_at')
                ->label('Créé le')
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->format('d/m/Y H:i:s')),
            ExportColumn::make('updatedBy.name')
                ->label('Modifié par'),
            ExportColumn::make('updated_at')
                ->label('Modifié le')
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->format('d/m/Y H:i:s')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your employee export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
