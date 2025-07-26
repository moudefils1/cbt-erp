<?php

namespace App\Providers\Filament;

use App\Filament\Clusters\Enter\Resources\ProductResource\Widgets\ElectronicProductsChart;
use App\Filament\Clusters\Enter\Resources\ProductResource\Widgets\LatestProduct;
use App\Filament\Clusters\Enter\Resources\ProductResource\Widgets\OtherProductsChart;
use App\Filament\Clusters\Enter\Resources\ProductResource\Widgets\ProductsChart;
use App\Filament\Clusters\Enter\Resources\ProductResource\Widgets\ProductsOverview;
use App\Filament\Clusters\Enter\Resources\ProductResource\Widgets\VehicleProductsChart;
use App\Filament\Resources\EmployeeResource\Widgets\EmployeesOverview;
use App\Filament\Resources\EmployeeResource\Widgets\LatestEmployees;
use App\Filament\Widgets\ChartByGender;
use App\Filament\Widgets\EmployeeAndInternshipOverview;
use App\Filament\Widgets\EmployeeByStatusChart;
use App\Filament\Widgets\ProductByCategoryChart;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\SpatieLaravelTranslatablePlugin;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('dashboard')
            ->databaseTransactions()
            ->path('')
            ->login()
            // ->passwordReset()
            // ->profile()
            ->colors([
                'primary' => Color::Sky,
            ])
            ->databaseNotifications()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->resources([
                config('filament-logger.activity_resource')
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,

                //                EmployeesOverview::class,
                //                ProductsChart::class,
                //                ElectronicProductsChart::class,
                //                VehicleProductsChart::class,
                //                OtherProductsChart::class,

                EmployeeAndInternshipOverview::class,
                ChartByGender::class,
                EmployeeByStatusChart::class,
                ProductByCategoryChart::class,
                // ProductsChart::class,

                //                ProductsOverview::class,

                //                LatestEmployees::class,
                //                LatestProduct::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
            ])
            ->plugins([
                FilamentShieldPlugin::make()
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3,
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 2,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ]),
                SpatieLaravelTranslatablePlugin::make()
                    ->defaultLocales(['fr']),
            ]);

    }
}
