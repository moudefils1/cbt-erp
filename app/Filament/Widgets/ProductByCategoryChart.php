<?php

namespace App\Filament\Widgets;

use App\Enums\ProductStatusEnum;
use App\Enums\ProductTypeEnum;
use App\Models\EmployeeProductItem;
use App\Models\Product;
use Filament\Widgets\ChartWidget;

class ProductByCategoryChart extends ChartWidget
{
    protected static ?string $heading = 'État des Produits par Catégorie';

    protected static ?string $maxHeight = '400';

    protected static ?string $description = 'Statistiques sur les produits par catégorie.';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $availableElectronics = Product::where('product_type_id', ProductTypeEnum::Electronic)->where('is_available', 1)->count();
        $availableVehicles = Product::where('product_type_id', ProductTypeEnum::Vehicle)->where('is_available', 1)->count();
        $availableOthers = Product::where('product_type_id', ProductTypeEnum::Other)->where('is_available', 1)->sum('quantity');

        $allocatedElectronics = Product::where('product_type_id', ProductTypeEnum::Electronic)->where('is_available', 0)->count();
        $allocatedVehicles = Product::where('product_type_id', ProductTypeEnum::Vehicle)->where('is_available', 0)->count();
        $allocatedOthers = EmployeeProductItem::where('product_type_id', ProductTypeEnum::Other)->where('is_active', 1)->sum('quantity');

        $reformedElectronics = EmployeeProductItem::where('product_type_id', ProductTypeEnum::Electronic)
            ->where('is_active', 0)
            ->where('state', ProductStatusEnum::Reformed)->count();
        $reformedVehicles = EmployeeProductItem::where('product_type_id', ProductTypeEnum::Vehicle)
            ->where('is_active', 0)
            ->where('state', ProductStatusEnum::Reformed)->count();
        $reformedOthers = 0; // Because no reformed products for the "Other" category, never used

        return [
            'datasets' => [
                [
                    'label' => 'Disponible',
                    'data' => [$availableElectronics, $availableVehicles, $availableOthers],
                    'backgroundColor' => 'rgba(54, 162, 235, 0.8)',
                    'borderColor' => 'rgb(54, 162, 235)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Attribué',
                    'data' => [$allocatedElectronics, $allocatedVehicles, $allocatedOthers],
                    'backgroundColor' => 'rgba(255, 99, 132, 0.8)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Réformé',
                    'data' => [$reformedElectronics, $reformedVehicles, $reformedOthers],
                    'backgroundColor' => 'rgba(255, 206, 86, 0.8)',
                    'borderColor' => 'rgb(255, 206, 86)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => [
                ProductTypeEnum::Electronic->getLabel(),
                ProductTypeEnum::Vehicle->getLabel(),
                ProductTypeEnum::Other->getLabel(),
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
                'x' => [
                    'grid' => ['display' => false],
                    'ticks' => ['font' => ['weight' => 'bold']],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'grid' => ['color' => 'rgba(0, 0, 0, 0.1)'],
                    'ticks' => ['stepSize' => 1],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                    'align' => 'center',
                    'labels' => [
                        'padding' => 20,
                        'font' => [
                            'size' => 12,
                            'family' => "'Helvetica', 'Arial', sans-serif",
                            'weight' => 'bold',
                        ],
                    ],
                ],
                'tooltip' => [
                    'enabled' => true,
                    'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                    'titleColor' => 'rgb(255, 255, 255)',
                    'bodyColor' => 'rgb(255, 255, 255)',
                    'padding' => 12,
                    'cornerRadius' => 6,
                    'callbacks' => [
                        'labels' => 'function(context) {
                            let label = context.dataset.label || "";
                            let value = context.parsed.y;
                            if (context.label.includes("Autres")) {
                                return `${label}: ${value} unités`;
                            }
                            return `${label}: ${value}`;
                        }',
                    ],
                ],
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
            'barPercentage' => 0.8,
            'categoryPercentage' => 0.9,
            'borderRadius' => 4,
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->can('widget_ProductByCategoryChart');
    }
}
