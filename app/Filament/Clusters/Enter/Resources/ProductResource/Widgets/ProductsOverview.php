<?php

namespace App\Filament\Clusters\Enter\Resources\ProductResource\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('En Stock', Product::where('product_type_id', 1)->where('is_available', 1)->count())
                ->icon('heroicon-o-computer-desktop')
                ->description('Matériels Électroniques')
                ->descriptionColor('success'),
            Stat::make('En Stock', Product::where('product_type_id', 2)->where('is_available', 1)->count())
                ->icon('heroicon-o-truck')
                ->description('Véhicules')
                ->descriptionColor('success'),
            Stat::make('En Stock', Product::where('product_type_id', 3)->where('is_available', 1)->sum('quantity'))
                ->icon('heroicon-o-shopping-cart')
                ->description('Vivres et Autres')
                ->descriptionColor('success'),
        ];
    }

    // canView()
    public static function canView(): bool
    {
        return auth()->user()->hasRole("super_admin") || auth()->user()->can('widget_ProductsOverview');
    }
}
