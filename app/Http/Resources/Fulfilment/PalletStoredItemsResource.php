<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 03 Feb 2024 11:07:20 Malaysia Time, Bali Airport, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $reference
 * @property mixed $stored_item_reference
 * @property mixed $stored_item_name
 * @property mixed $quantity
 */
class PalletStoredItemsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'stored_item_slug'      => $this->stored_item_slug,
            'stored_item_reference' => $this->stored_item_reference,
            'stored_item_name'      => $this->stored_item_name,
            'quantity'              => $this->quantity,
            'pallet_reference'      => $this->pallet_reference,
            'location_code'         => $this->location_code,
            'fulfilment_customer_slug' => $this->fulfilment_customer_slug,
            ...$this->resource->getStockReservations(),

        ];
    }
}
