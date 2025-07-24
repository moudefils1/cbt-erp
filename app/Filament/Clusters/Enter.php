<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Enter extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-left';

    protected static ?string $navigationGroup = 'Gestion des Matériels';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = "Bons d'Entrée";

    protected static ?string $clusterBreadcrumb = "Bons d'Entrée";

    /*public static function getNavigationBadge(): ?string
    {
        // return count of all supplied products
        return \App\Models\SuppliedProduct::count();
    }*/
}
