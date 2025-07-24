<?php

namespace App\Filament\Clusters\Enter\Resources\ProductResource\Widgets;

use App\Enums\ProductTypeEnum;
use App\Models\Product;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ProductsChart extends ChartWidget
{
    protected static ?string $heading = 'Évolution des Fournitures';

    protected static string $color = 'info';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '200';

    protected static ?array $options = [
        'plugins' => [
            'legend' => [
                'display' => true,
                'position' => 'bottom',
                'labels' => [
                    'padding' => 16,
                    'usePointStyle' => true,
                    'pointStyle' => 'circle',
                ],
            ],
            'tooltip' => [
                'mode' => 'index',
                'intersect' => false,
                'padding' => 12,
            ],
        ],
        'scales' => [
            'y' => [
                'beginAtZero' => true,
                'grid' => [
                    'display' => true,
                    'drawBorder' => false,
                ],
                'ticks' => [
                    'stepSize' => 1,
                ],
            ],
            'x' => [
                'grid' => [
                    'display' => false,
                ],
            ],
        ],
        'elements' => [
            'line' => [
                'tension' => 0.3,
                'borderWidth' => 2,
                'fill' => true,
            ],
            'point' => [
                'radius' => 4,
                'hoverRadius' => 6,
            ],
        ],
    ];

    protected function getData(): array
    {
        // First, get the data from Trend
        $data = Trend::model(Product::class, fn ($query) => $query->where('product_type_id', ProductTypeEnum::Other))
            ->between(
                start: now()->startOfMonth(),
                end: now()->endOfMonth(),
            )
            ->perDay()
            ->sum('quantity');

        // Then, return the properly structured array
        return [
            'labels' => $data->map(fn (TrendValue $value) => Carbon::parse($value->date)->format('d/m')),
            'datasets' => [
                [
                    'label' => 'Produits ajoutés',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'borderColor' => 'rgb(14, 165, 233)',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.1)',
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function getDescription(): ?string
    {
        return 'Statistiques mensuelles des fournitures ajoutées';
    }

    //    public function getColumnSpan(): int|string
    //    {
    //        return 'full';
    //    }

    public static function canView(): bool
    {
        return auth()->user()->can('widget_ProductsChart');
    }
}
