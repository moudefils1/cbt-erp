<?php

namespace App\Filament\Resources\EmployeeResource\Widgets;

use App\Enums\EmployeeStatusEnum;
use App\Enums\GenderEnum;
use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EmployeesOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Personnels', Employee::count())
                ->icon('heroicon-o-user-circle')
                ->description('Tous')
                ->descriptionColor('primary'),

            Stat::make('Personnels', Employee::where('status', EmployeeStatusEnum::WORKING)
                ->where('on_leave', false)
                ->where('on_training', false)
                ->count())
                ->icon('heroicon-o-user-circle')
                ->description('En Service')
                ->descriptionColor('success'),

            Stat::make('Personnels', Employee::where('status', EmployeeStatusEnum::CONTRACT_ENDED)->count())
                ->icon('heroicon-o-user-circle')
                ->description('Contrat Terminé')
                ->descriptionColor('primary'),

            Stat::make('Personnels', Employee::where('status', EmployeeStatusEnum::RESIGNED)->count())
                ->icon('heroicon-o-user-circle')
                ->description('Démissionnés')
                ->descriptionColor('warning'),

            Stat::make('Personnels', Employee::where('status', EmployeeStatusEnum::RETIRED)->count())
                ->icon('heroicon-o-user-circle')
                ->description('Retraités')
                ->descriptionColor('info'),

            Stat::make('Personnels', Employee::where('status', EmployeeStatusEnum::FIRED)->count())
                ->icon('heroicon-o-user-circle')
                ->description('Licenciés')
                ->descriptionColor('danger'),

            Stat::make('Personnels', Employee::where('status', EmployeeStatusEnum::DECEASED)->count())
                ->icon('heroicon-o-user-circle')
                ->description('Décédés')
                ->descriptionColor('gray'),

            Stat::make('Hommes', Employee::where('gender', GenderEnum::HOMME)->where('status', EmployeeStatusEnum::WORKING)->count())
                ->icon('heroicon-o-user-circle')
                ->description('En Service')
                ->descriptionColor('success'),

            Stat::make('Femmes', Employee::where('gender', GenderEnum::FEMME)->where('status', EmployeeStatusEnum::WORKING)->count())
                ->icon('heroicon-o-user-circle')
                ->description('En Service')
                ->descriptionColor('success'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->can('widget_EmployeesOverview');
    }
}
