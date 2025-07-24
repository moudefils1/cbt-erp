<?php

namespace App\Filament\Resources\Shield\RoleResource\Pages;

use App\Filament\Resources\Shield\RoleResource;
use App\Models\Role;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /*public function getTabs(): array
    {
        $tabs = [];

        $tabs['all'] = Tab::make('Tous')
            ->badge(Role::count());

        $tabs['trashed'] = Tab::make('Supprimés')
            ->badge(Role::query()->where('deleted_at', '!=', null)->count())
            ->badgeIcon('heroicon-o-trash')
            ->badgeColor('danger');

        return $tabs;
    }*/
}
