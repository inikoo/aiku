<?php
/*
 * author Arya Permana - Kirin
 * created on 15-11-2024-10h-07m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Procurement\OrgSupplierProduct;
use App\Models\SupplyChain\SupplierProduct;

/**
 * @property string $code
 * @property string $name
 * @property string $slug
 * @property string $created_at
 * @property string $updated_at
 * @property string $description
 */
class PurchaseOrderOrgSupplierProductsResource extends JsonResource
{
    public function toArray($request): array
    {

        $supplierProduct = SupplierProduct::find($this->supplier_product_id)->first();
        /** @var SupplierProduct $supplierProduct */
        return [
            'id'              => $this->id,
            'historic_id'     => $this->historic_id,
            'code'            => $this->code,
            'name'            => $this->name,
            'supplier_name'   => $supplierProduct->supplier->name,
            'image_thumbnail' => $supplierProduct->stock->imageSources(40, 40),
            'quantity_ordered' => $this->quantity_ordered ?? 0
        ];
    }
}
