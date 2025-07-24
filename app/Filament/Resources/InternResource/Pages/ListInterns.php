<?php

namespace App\Filament\Resources\InternResource\Pages;

use App\Enums\StateEnum;
use App\Filament\Resources\InternResource;
use App\Models\Intern;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListInterns extends ListRecords
{
    protected static string $resource = InternResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [];

        $tabs['all'] = Tab::make('Tous')
            ->badge(Intern::count());

        $tabs['progress'] = Tab::make('En cours')
            ->badge(Intern::where('status', StateEnum::IN_PROGRESS)->count())
            ->badgeIcon('heroicon-o-user-circle')
            ->badgeColor('info')
            ->modifyQueryUsing(function ($query) {
                return $query->where('status', StateEnum::IN_PROGRESS);
            });

        $tabs['completed'] = Tab::make('Terminés')
            ->badge(Intern::where('status', StateEnum::COMPLETED)->count())
            ->badgeIcon('heroicon-o-user-circle')
            ->badgeColor('success')
            ->modifyQueryUsing(function ($query) {
                return $query->where('status', StateEnum::COMPLETED);
            });

        $tabs['standby'] = Tab::make('En attente')
            ->badge(Intern::where('status', StateEnum::STANDBY)->count())
            ->badgeIcon('heroicon-o-user-circle')
            ->badgeColor('warning')
            ->modifyQueryUsing(function ($query) {
                return $query->where('status', StateEnum::STANDBY);
            });

        $tabs['trashed'] = Tab::make('Supprimés')
            ->badge(Intern::onlyTrashed()->count())
            ->badgeIcon('heroicon-o-trash')
            ->badgeColor('danger')
            ->modifyQueryUsing(function ($query) {
                return $query->onlyTrashed();
            });

        return $tabs;
    }
}
