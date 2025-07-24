<?php

namespace App\Filament\Clusters\Enter\Resources\ProductResource\Pages;

use App\Filament\Clusters\Enter\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // parce que chaque produit est lié à une facture, il est préférable de créer un produit à partir de la page de détail de la facture
            // Actions\CreateAction::make(),
        ];
    }

    /*protected function getHeaderWidgets(): array
    {
        return [
            ProductResource\Widgets\ProductsOverview::make(),
        ];
    }*/

    public function getTabs(): array
    {
        $tabs = [];

        $tabs['all'] = Tab::make('Total')
            ->badge(Product::count());

        $tabs['available'] = Tab::make('Disponibles')
            ->badge(Product::where('is_available', 1)->count())
            ->badgeIcon('heroicon-o-check-circle')
            ->badgeColor('success')
            ->modifyQueryUsing(function ($query) {
                return $query->where('is_available', 1);
            });

        $tabs['Attributed'] = Tab::make('Attribués')
            ->badge(Product::where('is_available', 0)->count())
            ->badgeIcon('heroicon-o-x-circle')
            ->badgeColor('danger')
            ->modifyQueryUsing(function ($query) {
                return $query->where('is_available', 0);
            });

        $tabs['deleted'] = Tab::make('Supprimés')
            ->badge(Product::onlyTrashed()->count())
            ->badgeIcon('heroicon-o-trash')
            ->badgeColor('danger')
            ->modifyQueryUsing(function ($query) {
                return $query->onlyTrashed();
            });

        return $tabs;
    }
}
