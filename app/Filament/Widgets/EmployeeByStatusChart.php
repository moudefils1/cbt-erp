<?php

namespace App\Filament\Widgets;

use App\Enums\EmployeeStatusEnum;
use App\Models\Employee;
use Filament\Widgets\ChartWidget;

class EmployeeByStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Répartition des Personnels par Statut';

    protected static ?string $description = 'Statistiques sur le statut des personnels.';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $employeeWorkingCount = Employee::where('status', EmployeeStatusEnum::WORKING)
            ->where('on_leave', false)
            ->where('on_training', false)
            ->count();
        $employeeOnLeaveCount = Employee::where('status', EmployeeStatusEnum::WORKING)
            ->where('on_leave', true)
            ->count();
        $employeeInTrainingCount = Employee::where('status', EmployeeStatusEnum::WORKING)
            ->where('on_training', true)
            ->count();
        $employeeContractEndedCount = Employee::where('status', EmployeeStatusEnum::CONTRACT_ENDED)->count();
        $employeeResignedCount = Employee::where('status', EmployeeStatusEnum::RESIGNED)->count();
        $employeeRetiredCount = Employee::where('status', EmployeeStatusEnum::RETIRED)->count();
        $employeeFiredCount = Employee::where('status', EmployeeStatusEnum::FIRED)->count();
        $employeeDeceasedCount = Employee::where('status', EmployeeStatusEnum::DECEASED)->count();

        return [
            'datasets' => [
                [
                    'label' => 'Nombre de Personnels',
                    'data' => [
                        $employeeWorkingCount,
                        $employeeOnLeaveCount,
                        $employeeInTrainingCount,
                        $employeeContractEndedCount,
                        $employeeResignedCount,
                        $employeeRetiredCount,
                        $employeeFiredCount,
                        $employeeDeceasedCount,
                    ],
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.8)',  // success - working
                        'rgba(245, 158, 11, 0.8)', // warning - on leave
                        'rgba(245, 158, 11, 0.8)', // warning - in training
                        'rgba(59, 130, 246, 0.8)', // primary - contract ended
                        'rgba(239, 68, 68, 0.8)', // warning - resigned
                        'rgba(6, 182, 212, 0.8)',  // info - retired
                        'rgba(239, 68, 68, 0.8)',  // danger - fired
                        'rgba(107, 114, 128, 0.8)', // gray - deceased
                    ],
                    'borderColor' => 'white',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => [
                EmployeeStatusEnum::WORKING->getLabel(),
                EmployeeStatusEnum::ON_LEAVE->getLabel(),
                EmployeeStatusEnum::IN_TRAINING->getLabel(),
                EmployeeStatusEnum::CONTRACT_ENDED->getLabel(),
                EmployeeStatusEnum::RESIGNED->getLabel(),
                EmployeeStatusEnum::RETIRED->getLabel(),
                EmployeeStatusEnum::FIRED->getLabel(),
                EmployeeStatusEnum::DECEASED->getLabel(),
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'aspectRatio' => 1,
            'indexAxis' => 'y',
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(0,0,0,0.1)',
                    ],
                    'ticks' => ['stepSize' => 1],
                ],
                'y' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'enabled' => true,
                    'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                    'padding' => 12,
                    'titleColor' => 'rgb(255, 255, 255)',
                    'bodyColor' => 'rgb(255, 255, 255)',
                    'titleFont' => [
                        'size' => 14,
                    ],
                    'bodyFont' => [
                        'size' => 13,
                    ],
                    'displayColors' => true,
                ],
            ],
            'barThickness' => 25, // old: 40
            'maxBarThickness' => 20, // old: 50
            'borderRadius' => 4,
            'barPercentage' => 0.85, // not exist
            'categoryPercentage' => 0.85, // not exist
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->can('widget_EmployeeByStatusChart');
    }
}
