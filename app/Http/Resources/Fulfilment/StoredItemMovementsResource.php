<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 03 Feb 2024 11:07:20 Malaysia Time, Bali Airport, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Fulfilment\StoredItemAudit;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $reference
 * @property mixed $customer_reference
 * @property mixed $fulfilment_customer_id
 * @property mixed $slug
 * @property mixed $notes
 * @property mixed $state
 * @property mixed $type
 * @property mixed $storedItems
 * @property mixed $rental_id
 * @property mixed $status
 * @property mixed $location_slug
 * @property mixed $location_code
 * @property mixed $location_id
 * @property mixed $warehouse_id
 * @property mixed $pallet_delivery_id
 * @property mixed $pallet_return_id
 * @property mixed $fulfilment_customer_name
 * @property mixed $fulfilment_customer_slug
 */
class StoredItemMovementsResource extends JsonResource
{
    public function toArray($request): array
    {
        $desc_model = '';
        $desc_title = '';
        $desc_after_title = '';
        $desc_route = null;

        if ($this->stored_item_audit_reference) {
            $storedItem = StoredItemAudit::where('reference', $this->stored_item_audit_reference)->first();
            if ($storedItem) {
                $desc_title = $storedItem->reference;
                $desc_model = __('Stored Item Audit');
            }
        } elseif ($this->pallet_delivery_reference) {
            $palletDelivery = PalletDelivery::where('reference', $this->pallet_delivery_reference)->first();
            if ($palletDelivery) {
                $desc_title = $palletDelivery->reference;
                $desc_model = __('Stored Item Audit');
            }
        } elseif ($this->pallet_returns_reference) {
            $palletReturn = PalletReturn::where('reference', $this->pallet_returns_reference)->first();
            if ($palletReturn) {
                $desc_title = $palletReturn->reference;
                $desc_model = __('Stored Item Audit');
            }
        } else {
            $desc_title = '-';
            $desc_model = __('No Parent');
        }

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'reference' => $this->reference,
            'quantity' => $this->quantity,
            'pallet_reference' => $this->pallet_reference,
            'pallet_slug' => $this->pallet_slug,
            'type' => $this->type,
            'location_slug' => $this->location_slug,
            'location_code' => $this->location_code,
            'description' => [
                'model' => $desc_model,
                'title' => $desc_title,
                'route' => $desc_route,
                'after_title' => $desc_after_title,
            ]
        ];
    }
}
