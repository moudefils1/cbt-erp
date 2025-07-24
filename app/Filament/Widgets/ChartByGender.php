<?php

namespace App\Filament\Widgets;

use App\Enums\GenderEnum;
use App\Models\Employee;
use App\Models\Intern;
use Filament\Widgets\ChartWidget;

class ChartByGender extends ChartWidget
{
    protected static ?string $heading = 'Répartition par Genre des Personnels et Stagiaires';

    protected static ?string $description = 'Statistiques sur le genre des personnels et stagiaires.';

    // protected int | string | array $columnSpan = "full";
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $employeeHommeCount = Employee::where('gender', GenderEnum::HOMME)->count();
        $employeeFemmeCount = Employee::where('gender', GenderEnum::FEMME)->count();
        $internHommeCount = Intern::where('gender', GenderEnum::HOMME)->count();
        $internFemmeCount = Intern::where('gender', GenderEnum::FEMME)->count();

        return [
            'datasets' => [
                [
                    'label' => 'Personnels (H)',
                    'data' => [$employeeHommeCount, 0],
                    'backgroundColor' => 'rgba(54, 162, 235, 0.8)', // Blue for male employees
                    'borderColor' => 'white',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Personnels (F)',
                    'data' => [0, $employeeFemmeCount],
                    'backgroundColor' => 'rgba(255, 99, 132, 0.8)', // Pink for female employees
                    'borderColor' => 'white',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Stagiaires (H)',
                    'data' => [$internHommeCount, 0],
                    'backgroundColor' => 'rgba(75, 192, 192, 0.8)', // Teal for male interns
                    'borderColor' => 'white',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Stagiaires (F)',
                    'data' => [0, $internFemmeCount],
                    'backgroundColor' => 'rgba(255, 159, 64, 0.8)', // Orange for female interns
                    'borderColor' => 'white',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => [
                GenderEnum::HOMME->getLabel(),
                GenderEnum::FEMME->getLabel(),
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
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(0,0,0,0.1)',
                    ],
                    'ticks' => ['stepSize' => 1],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 20,
                        'font' => [
                            'size' => 12,
                            'family' => "'Helvetica', 'Arial', sans-serif",
                        ],
                    ],
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
            'barThickness' => 40,
            'maxBarThickness' => 50,
            'borderRadius' => 4,
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->can('widget_ChartByGender');
    }
}
