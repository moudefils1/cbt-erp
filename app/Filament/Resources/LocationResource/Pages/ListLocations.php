<?php

namespace App\Filament\Resources\LocationResource\Pages;

use App\Filament\Resources\LocationResource;
use App\Models\Location;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListLocations extends ListRecords
{
    protected static string $resource = LocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['created_by'] = auth()->id();

                    return $data;
                }),
        ];
    }

    //    public function getTabs(): array
    //    {
    //        $tabs = [];
    //
    //        $tabs['all'] = Tab::make('Tous')
    //            ->badge(Location::count());
    //
    //        $tabs['deleted'] = Tab::make('Supprimés')
    //            ->badge(Location::onlyTrashed()->count())
    //            ->badgeIcon('heroicon-o-trash')
    //            ->badgeColor('danger')
    //            ->modifyQueryUsing(function ($query) {
    //                return $query->onlyTrashed();
    //            });
    //
    //        return $tabs;
    //    }
}
