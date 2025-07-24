<?php

namespace App\Filament\Resources\LeaveResource\Pages;

use App\Enums\LeaveEnum;
use App\Enums\StateEnum;
use App\Filament\Resources\LeaveResource;
use App\Models\EmployeeLeaveBalance;
use App\Models\Leave;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListLeaves extends ListRecords
{
    protected static string $resource = LeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalHeading('Ajouter un Congé')
                ->mutateFormDataUsing(function (array $data) {
                    $data['created_by'] = auth()->id();

                    $start = \Carbon\Carbon::parse($data['start_date']);
                    $end = \Carbon\Carbon::parse($data['end_date']);
                    $used_days = 0;

                    for ($date = $start; $date->lte($end); $date->addDay()) {
                        // Skip weekends (0 = Sunday, 6 = Saturday)
                        if (! in_array($date->dayOfWeek, [0, 6])) {
                            $used_days++;
                        }
                    }

                    $data['used_days'] = $used_days;

                    $data['employee_leave_balance_id'] = EmployeeLeaveBalance::where('leave_type_id', $data['leave_type_id'])
                        ->where('employee_id', $data['employee_id'])
                        ->latest()
                        ->first()->id;

                    // if end date is passed, then the leave is approved
                    if (\Carbon\Carbon::parse($data['end_date'])->isPast()) {
                        $data['status'] = LeaveEnum::APPROVED;
                        $data['approved_by'] = auth()->id();
                        $data['approved_at'] = now();
                        $data['state'] = StateEnum::COMPLETED;

                        // update leave balance
                        $employee_leave_balance = EmployeeLeaveBalance::where('leave_type_id', $data['leave_type_id'])
                            ->where('employee_id', $data['employee_id'])
                            ->latest()
                            ->first();

                        $employee_leave_balance->update([
                            'used_days' => $employee_leave_balance->used_days + $used_days,
                        ]);
                    } else {
                        $data['status'] = LeaveEnum::PENDING;
                    }

                    return $data;
                }),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [];

        $tabs['all'] = Tab::make('Total')
            ->badge(Leave::count());

        $tabs['pending'] = Tab::make('En attente')
            ->badge(Leave::where('status', LeaveEnum::PENDING)->count())
            ->badgeIcon('heroicon-o-calendar-days')
            ->badgeColor('warning')
            ->modifyQueryUsing(function ($query) {
                return $query->where('status', LeaveEnum::PENDING);
            });

        $tabs['approved'] = Tab::make('Approuvé')
            ->badge(Leave::where('status', LeaveEnum::APPROVED)->count())
            ->badgeIcon('heroicon-o-calendar-days')
            ->badgeColor('success')
            ->modifyQueryUsing(function ($query) {
                return $query->where('status', LeaveEnum::APPROVED);
            });

        $tabs['rejected'] = Tab::make('Rejeté')
            ->badge(Leave::where('status', LeaveEnum::REJECTED)->count())
            ->badgeIcon('heroicon-o-calendar-days')
            ->badgeColor('danger')
            ->modifyQueryUsing(function ($query) {
                return $query->where('status', LeaveEnum::REJECTED);
            });

        return $tabs;
    }
}
