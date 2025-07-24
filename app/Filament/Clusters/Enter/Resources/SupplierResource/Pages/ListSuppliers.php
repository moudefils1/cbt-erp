<?php

namespace App\Filament\Clusters\Enter\Resources\SupplierResource\Pages;

use App\Filament\Clusters\Enter\Resources\SupplierResource;
use App\Models\Supplier;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListSuppliers extends ListRecords
{
    protected static string $resource = SupplierResource::class;

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

    public function getTabs(): array
    {
        $tabs = [];

        $tabs['all'] = Tab::make('Tous')
            ->badge(Supplier::count());

        $tabs['active'] = Tab::make('Actifs')
            ->badge(Supplier::where('is_active', 1)->count())
            ->badgeIcon('heroicon-o-check-circle')
            ->badgeColor('success')
            ->modifyQueryUsing(function ($query) {
                return $query->where('is_active', 1);
            });

        $tabs['inactive'] = Tab::make('Passifs')
            ->badge(Supplier::where('is_active', 0)->count())
            ->badgeIcon('heroicon-o-x-circle')
            ->badgeColor('danger')
            ->modifyQueryUsing(function ($query) {
                return $query->where('is_active', 0);
            });

        $tabs['deleted'] = Tab::make('Supprimés')
            ->badge(Supplier::onlyTrashed()->count())
            ->badgeIcon('heroicon-o-trash')
            ->badgeColor('danger')
            ->modifyQueryUsing(function ($query) {
                return $query->onlyTrashed();
            });

        return $tabs;
    }
}
