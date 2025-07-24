<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Enums\EmployeeStatusEnum;
use App\Filament\Resources\EmployeeResource;
use App\Models\Employee;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [];

        $tabs['all'] = Tab::make('Total')
            ->badge(Employee::count());

        $tabs['working'] = Tab::make('En service')
            ->badge(Employee::where('status', EmployeeStatusEnum::WORKING)
                ->where('on_leave', false)
                ->where('on_training', false)
                ->count())
            ->badgeIcon('heroicon-o-user-circle')
            ->badgeColor('success')
            ->modifyQueryUsing(function ($query) {
                return $query->where('status', EmployeeStatusEnum::WORKING)
                    ->where('on_leave', false)
                    ->where('on_training', false);
            });

        $tabs['on_leave'] = Tab::make('En congé')
            ->badge(Employee::where('status', EmployeeStatusEnum::WORKING)
                ->where('on_leave', true)
                ->count())
            ->badgeIcon('heroicon-o-calendar-days')
            ->badgeColor('warning')
            ->modifyQueryUsing(function ($query) {
                return $query->where('status', EmployeeStatusEnum::WORKING)
                    ->where('on_leave', true);
            });

        $tabs['in_training'] = Tab::make('En formation')
            ->badge(Employee::where('status', EmployeeStatusEnum::WORKING)
                ->where('on_training', true)
                ->count())
            ->badgeIcon('heroicon-o-user-circle')
            ->badgeColor('warning')
            ->modifyQueryUsing(function ($query) {
                return $query->where('status', EmployeeStatusEnum::WORKING)
                    ->where('on_training', true);
            });

        $tabs['contract_ended'] = Tab::make('Contrat terminé')
            ->badge(Employee::where('status', EmployeeStatusEnum::CONTRACT_ENDED)->count())
            ->badgeIcon('heroicon-o-user-circle')
            ->badgeColor('primary')
            ->modifyQueryUsing(function ($query) {
                return $query->where('status', EmployeeStatusEnum::CONTRACT_ENDED);
            });

        $tabs['resigned'] = Tab::make('Démissionné')
            ->badge(Employee::where('status', EmployeeStatusEnum::RESIGNED)->count())
            ->badgeIcon('heroicon-o-user-circle')
            ->badgeColor('danger')
            ->modifyQueryUsing(function ($query) {
                return $query->where('status', EmployeeStatusEnum::RESIGNED);
            });

        $tabs['retired'] = Tab::make('Retraité')
            ->badge(Employee::where('status', EmployeeStatusEnum::RETIRED)->count())
            ->badgeIcon('heroicon-o-user-circle')
            ->badgeColor('info')
            ->modifyQueryUsing(function ($query) {
                return $query->where('status', EmployeeStatusEnum::RETIRED);
            });

        $tabs['fired'] = Tab::make('Licencié')
            ->badge(Employee::where('status', EmployeeStatusEnum::FIRED)->count())
            ->badgeIcon('heroicon-o-user-circle')
            ->badgeColor('danger')
            ->modifyQueryUsing(function ($query) {
                return $query->where('status', EmployeeStatusEnum::FIRED);
            });

        $tabs['deceased'] = Tab::make('Décédé')
            ->badge(Employee::where('status', EmployeeStatusEnum::DECEASED)->count())
            ->badgeIcon('heroicon-o-user-circle')
            ->badgeColor('gray')
            ->modifyQueryUsing(function ($query) {
                return $query->where('status', EmployeeStatusEnum::DECEASED);
            });

        return $tabs;
    }
}
