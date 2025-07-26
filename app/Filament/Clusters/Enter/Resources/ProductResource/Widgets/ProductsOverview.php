<?php

    namespace App\Filament\Clusters\Enter\Resources\ProductResource\Widgets;

    use App\Models\Product;
    use Filament\Widgets\StatsOverviewWidget as BaseWidget;
    use Filament\Widgets\StatsOverviewWidget\Stat;
    use Illuminate\Support\HtmlString;

    class ProductsOverview extends BaseWidget
    {
        protected int | string | array $columnSpan = 'full';

        protected function getStats(): array
            {
                return [
                    // Matériels Électroniques
                    Stat::make('Matériels Électroniques', null)
                        ->icon('heroicon-o-computer-desktop')
                        ->color('primary')
                        ->chart([
                            Product::where('product_type_id', 1)->where('is_available', 1)->count(),
                            Product::where('product_type_id', 1)->where('is_available', 0)->count(),
                        ])
                        ->extraAttributes([
                            'class' => 'ring-2 ring-primary-50 shadow-md',
                        ])
                        ->description(new HtmlString("
                            <span style='color: green;'>Disponible(s): " . Product::where('product_type_id', 1)->where('is_available', 1)->count() . "</span><br>
                            <span style='color: red;'>Attribué(s): " . Product::where('product_type_id', 1)->where('is_available', 0)->count() . "</span>")
                            ),

                    // Véhicules
                    Stat::make('Véhicules', null)
                        ->icon('heroicon-o-truck')
                        ->color('warning')
                        ->chart([
                            Product::where('product_type_id', 2)->where('is_available', 1)->count(),
                            Product::where('product_type_id', 2)->where('is_available', 0)->count(),
                        ])
                        ->extraAttributes([
                            'class' => 'ring-2 ring-warning-50 shadow-md',
                        ])
//                        ->description(
//                            'En Stock: ' . Product::where('product_type_id', 2)->where('is_available', 1)->count() .
//                            "\nAttribué(s): " . Product::where('product_type_id', 2)->where('is_available', 0)->count()
//                        ),
                        ->description(new HtmlString("
                            <span style='color: green;'>Disponible(s): " . Product::where('product_type_id', 2)->where('is_available', 1)->count() . "</span><br>
                            <span style='color: red;'>Attribué(s): " . Product::where('product_type_id', 2)->where('is_available', 0)->count() . "</span>")
                        ),

                    // Vivres et Autres
                    Stat::make('Vivres et Autres', null)
                        ->icon('heroicon-o-shopping-cart')
                        ->color('success')
                        ->chart([
                            Product::where('product_type_id', 3)->where('is_available', 1)->sum('quantity'),
                            Product::where('product_type_id', 3)->where('is_available', 0)->sum('quantity'),
                        ])
                        ->extraAttributes([
                            'class' => 'ring-2 ring-success-50 shadow-md',
                        ])
//                        ->description(
//                            'En Stock: ' . Product::where('product_type_id', 3)->where('is_available', 1)->sum('quantity') .
//                            "\nAttribué(s): " . Product::where('product_type_id', 3)->where('is_available', 0)->sum('quantity')
//                        ),
                        ->description(new HtmlString("
                            <span style='color: green;'>Disponible(s): " . Product::where('product_type_id', 3)->where('is_available', 1)->sum('quantity') . "</span><br>
                            <span style='color: red;'>Attribué(s): " . Product::where('product_type_id', 3)->where('is_available', 0)->sum('quantity') . "</span>")
                        ),
                ];
            }

        public static function canView(): bool
        {
            return auth()->user()->hasRole("super_admin") || auth()->user()->can('widget_ProductsOverview');
        }
    }
