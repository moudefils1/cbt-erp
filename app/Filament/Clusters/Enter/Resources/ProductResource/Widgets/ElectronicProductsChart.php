<?php

namespace App\Filament\Clusters\Enter\Resources\ProductResource\Widgets;

use App\Enums\ProductTypeEnum;
use App\Models\Product;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ElectronicProductsChart extends ChartWidget
{
    protected static ?string $heading = 'Matériels Électroniques Fournis';

    protected function getData(): array
    {
        return [
            $data = Trend::model(Product::class)
                ->query(Product::query()
                    ->where('product_type_id', ProductTypeEnum::Electronic))
                ->between(
                    start: now()->startOfMonth(),
                    end: now()->endOfMonth(),
                )
                ->perDay()
                ->count(),
            'datasets' => [
                [
                    'label' => 'Produits ajoutés',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => Carbon::parse($value->date)->format('d/m/Y')),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    public static function canView(): bool
    {
        return auth()->user()->can('widget_ElectronicProductsChart');
    }
}
