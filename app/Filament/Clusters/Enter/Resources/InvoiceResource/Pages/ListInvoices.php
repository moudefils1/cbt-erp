<?php

namespace App\Filament\Clusters\Enter\Resources\InvoiceResource\Pages;

use App\Filament\Clusters\Enter\Resources\InvoiceResource;
use App\Filament\Clusters\Enter\Resources\ProductResource\Widgets\ProductsOverview;
use App\Models\Invoice;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [];

        $tabs['all'] = Tab::make('Toutes')
            ->badge(Invoice::count());

        $tabs['final'] = Tab::make('Complètes')
            ->badge(Invoice::where('invoice_status', 1)->count())
            ->badgeIcon('heroicon-o-check-circle')
            ->badgeColor('success')
            ->modifyQueryUsing(function ($query) {
                return $query->where('invoice_status', 1);
            });

        $tabs['draft'] = Tab::make('Incomplètes')
            ->badge(Invoice::where('invoice_status', 0)->count())
            ->badgeIcon('heroicon-o-x-circle')
            ->badgeColor('warning')
            ->modifyQueryUsing(function ($query) {
                return $query->where('invoice_status', 0);
            });

        $tabs['trashed'] = Tab::make('Supprimées')
            ->badge(Invoice::onlyTrashed()->count())
            ->badgeIcon('heroicon-o-trash')
            ->badgeColor('danger')
            ->modifyQueryUsing(function ($query) {
                return $query->onlyTrashed();
            });

        return $tabs;
    }
}
