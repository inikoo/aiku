<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 03 Feb 2024 11:07:20 Malaysia Time, Bali Airport, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use App\Models\Billables\Rental;
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
        $rental = Rental::find($this->rental_id) ?? null;

        return [
            'id'                       => $this->id,
            'slug'                     => $this->slug,
            'reference'                => $this->reference,
            'quantity'                => $this->quantity,
            'pallet_reference'       => $this->pallet_reference,
            'pallet_slug'       => $this->pallet_slug,
            'type'                     => $this->type,
            'location_slug'            => $this->location_slug,
            'location_code'            => $this->location_code,
            'parent'                => match (true) {
                !empty($this->stored_item_audit_reference)   => $this->stored_item_audit_reference,
                !empty($this->stored_item_audit_delta_id)      => $this->stored_item_audit_delta_id,
                !empty($this->pallet_delivery_reference)       => $this->pallet_delivery_reference,
                !empty($this->pallet_returns_reference)        => $this->pallet_returns_reference,
                default                                       => null,
            },
        ];
    }
}
