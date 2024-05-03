<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 11:30:19 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

/**
 * @property string $number
 * @property string $provider
 * @property string $state
 * @property string $status
 * @property string $created_at
 * @property string $updated_at
 * @property string $slug
 * @property string $date
 * @property mixed $parent_type
 * @property mixed $parent_id
 * @property mixed $parent
 */
class PurchaseOrdersResource extends JsonResource
{
    public function toArray($request): array
    {

        $parentData= $this->getParentData();

        return [
            'number'      => $this->number,
            'slug'        => $this->slug,
            'date'        => $this->date,
            'parent_name' => $parentData['name']
        ];
    }


    private function getParentData(): array
    {
        return Cache::remember("table_item_purchase_order_parent_{$this->parent_type}_$this->parent_id", 1, function () {
            $purchaseOrder = $this;

            $parent_name = match ($purchaseOrder->parent_type) {
                'OrgAgent'    => $purchaseOrder->parent->agent->organisation->name,
                'OrgSupplier' => $purchaseOrder->parent->supplier->name,
                'OrgPartner'  => $purchaseOrder->parent->partner->name,
                default       => null,
            };

            return [
                'name' => $parent_name
            ];
        });


    }

}
