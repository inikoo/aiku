<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 11:30:19 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \App\Models\SupplyChain\SupplierProduct $supplierProduct
 * @property string $data
 * @property float $unit_quantity
 * @property float $unit_price
 * @property string $created_at
 * @property string $updated_at
 */
class PurchaseOrderTransactionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'code'          => $this->supplierProduct->code,
            'name'          => $this->supplierProduct->name,
            'unit_quantity' => $this->unit_quantity,
            'unit_price'    => $this->unit_price,
            'unit_cost'     => $this->supplierProduct->cost,
            'state'         => $this->supplierProduct->state,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
