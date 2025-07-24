<?php

namespace App\Filament\Widgets;

use App\Enums\EmployeeStatusEnum;
use App\Enums\StateEnum;
use App\Models\Employee;
use App\Models\Guest;
use App\Models\Intern;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EmployeeAndInternshipOverview extends BaseWidget
{
    protected function getColumns(): int
    {
        $visibleStats = 0;
        if (auth()->user()->can('view_any_employee')) {
            $visibleStats++;
        }
        if (auth()->user()->can('view_any_intern')) {
            $visibleStats++;
        }
//        if (auth()->user()->can('view_any_guest')) {
//            $visibleStats++;
//        }
        if (auth()->user()->can('view_any_employee')) {
            $visibleStats++;
        } // For on-leave employees
        if (auth()->user()->can('view_any_training')) {
            $visibleStats++;
        } // For training stats

        return min($visibleStats, 4); // Increased maximum to accommodate new stat
    }

    protected function getStats(): array
    {
        $stats = [];
        $user = auth()->user();

        // Add stat for all employees
        if ($user->can('view_any_employee')) {
            $query = Employee::where('status', EmployeeStatusEnum::WORKING)
                ->where('on_leave', false)
                ->where('on_training', false);
            if (! $user->can('view_all_employee')) {
                $query->where('created_by', $user->id);
            }
            $stats[] = Stat::make('Personnels', $query->count())
                ->icon('heroicon-o-user-group')
                ->description('En Service')
                ->descriptionColor('success');

            // Add stat for employees on leave
            $query = Employee::where('status', EmployeeStatusEnum::WORKING)
                ->where('on_leave', true);
            if (! $user->can('view_all_employee')) {
                $query->where('created_by', $user->id);
            }
            $stats[] = Stat::make('Personnels', $query->count())
                ->icon('heroicon-o-calendar-days')
                ->description('En Congé')
                ->descriptionColor('warning');
        }

        // Add stat for employees on training
        if ($user->can('view_any_training')) {
            $query = Employee::where('status', EmployeeStatusEnum::WORKING)
                ->where('on_training', true);
            if (! $user->can('view_all_employee')) {
                $query->where('created_by', $user->id);
            }
            $stats[] = Stat::make('Personnels', $query->count())
                ->icon('heroicon-o-trophy')
                ->description('En Formation')
                ->descriptionColor('warning');
        }

        // Add stat for interns who in progress
        if ($user->can('view_any_intern')) {
            $query = Intern::where('status', StateEnum::IN_PROGRESS);
            if (! $user->can('view_all_intern')) {
                $query->where('created_by', $user->id);
            }
            $stats[] = Stat::make('Stagiaires', $query->count())
                ->icon('heroicon-o-square-3-stack-3d')
                ->description('Actifs')
                ->descriptionColor('success');
        }

        // Add stat for guests who in progress
//        if ($user->can('view_any_guest')) {
//            $query = Guest::where('status', StateEnum::IN_PROGRESS);
//            if (! $user->can('view_all_guest')) {
//                $query->where('created_by', $user->id);
//            }
//            $stats[] = Stat::make('Invités', $query->count())
//                ->icon('heroicon-o-sparkles')
//                ->description('Actifs')
//                ->descriptionColor('success');
//        }

        return $stats;
    }

    public static function canView(): bool
    {
        return auth()->user()->can('widget_EmployeeAndInternshipOverview');
    }
}
