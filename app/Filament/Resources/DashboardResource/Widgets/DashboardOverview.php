<?php

namespace App\Filament\Resources\DashboardResource\Widgets;

use App\Models\Electronic;
use App\Models\Vehicle;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardOverview extends BaseWidget
{
    // protected int | string | array $columnSpan = 3;

    protected function getStats(): array
    {
        return [
            Stat::make('Matériels Electroniques', Electronic::count())
                ->icon('heroicon-o-computer-desktop'),
            Stat::make('Véhicules', Vehicle::count())
                ->icon('heroicon-o-truck'),
            Stat::make('Vivres', 0)
                ->icon('heroicon-o-cube'),
        ];
    }
}
