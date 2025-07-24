<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Sortie extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-right';

    protected static ?string $navigationGroup = 'Gestion des Matériels';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Bons de Sortie';

    protected static ?string $clusterBreadcrumb = 'Bon de Sortie';

    /*public static function getNavigationBadge(): ?string
    {
        // return count of all assigned products
        return \App\Models\AssignedProduct::count();
    }*/
}
