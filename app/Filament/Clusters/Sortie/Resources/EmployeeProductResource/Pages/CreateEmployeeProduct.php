<?php

namespace App\Filament\Clusters\Sortie\Resources\EmployeeProductResource\Pages;

use App\Filament\Clusters\Sortie\Resources\EmployeeProductResource;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeProduct extends CreateRecord
{
    protected static string $resource = EmployeeProductResource::class;

    public function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        // dd($data);
        return $data;
    }

    /*protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        $product = Product::find($data['product_id']);
        $productTypeId = $product->product_type_id->value;

        if (in_array($productTypeId, [1, 2])) { // 1 = 'Materiel', 2 = 'Véhicule'
            $productStatus = 0;
            $newProductQuantity = $product->quantity;
        } else if ($productTypeId == 3) { // 3 = 'Vivres et Autres'
            // Check if the data quantity is greater than the product quantity
            if ($data['quantity'] > $product->quantity) {
                throw new \Exception('La quantité de produit à attribuer est supérieure à la quantité de produit disponible');
            }else{
                $productStatus = 1;
                $newProductQuantity = $product->quantity - $data['quantity'];
                if ($newProductQuantity == 0) {
                    $productStatus = 0;
                }
            }
        }

        //dd($data);

        $product->update([
            'quantity' => $newProductQuantity ?? $product->quantity,
            'is_available' => $productStatus,
            'updated_by' => auth()->id(),
        ]);

        return $data;
    }*/
}
