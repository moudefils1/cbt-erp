<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use App\Models\Task;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function ($data) {
                    $data['created_by'] = auth()->id();

                    return $data;
                }),
        ];
    }

    //    public function getTabs(): array
    //    {
    //        $tabs = [];
    //
    //        $tabs['all'] = Tab::make('Total')
    //            ->badge(Task::count());
    //
    //        $tabs['available'] = Tab::make('Disponibles')
    //            ->badge(Task::where('is_available', 1)->count())
    //            ->badgeIcon('heroicon-o-check-circle')
    //            ->badgeColor('success')
    //            ->modifyQueryUsing(function ($query) {
    //                return $query->where('is_available', 1);
    //            });
    //
    //        $tabs['unavailable'] = Tab::make('Occupés')
    //            ->badge(Task::where('is_available', 0)->count())
    //            ->badgeIcon('heroicon-o-x-circle')
    //            ->badgeColor('danger')
    //            ->modifyQueryUsing(function ($query) {
    //                return $query->where('is_available', 0);
    //            });
    //
    //        $tabs['deleted'] = Tab::make('Supprimés')
    //            ->badge(Task::onlyTrashed()->count())
    //            ->badgeIcon('heroicon-o-trash')
    //            ->badgeColor('danger')
    //            ->modifyQueryUsing(function ($query) {
    //                return $query->onlyTrashed();
    //            });
    //
    //        return $tabs;
    //    }
}
