<?php

/*
 * author Arya Permana - Kirin
 * created on 09-01-2025-11h-54m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Http\Resources\Fulfilment;

use App\Http\Resources\Inventory\LocationResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $slug
 * @property string $customer_reference
 * @property string $state
 * @property string $status
 * @property string $notes
 * @property \App\Models\Fulfilment\FulfilmentCustomer $fulfilmentCustomer
 * @property \App\Models\Inventory\Location $location
 * @property \App\Models\Inventory\Warehouse $warehouse
 * @property \App\Models\Fulfilment\StoredItem $storedItems
 */
class RetinaDropshippingFulfilmentOrdersResources extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                    => $this->id,
            'reference'             => $this->reference,
            'customer_reference'    => $this->customer_reference,
            'slug'                  => $this->slug ?? null,
            'location'              => LocationResource::make($this->location),
            'state'                 => $this->state,
            'status'                => $this->status,
            'notes'                 => $this->notes ?? '',
            'rental_id'             => $this->rental_id,
            'status_label'          => $pallet->status->labels()[$pallet->status->value],
            'status_icon'           => $pallet->status->statusIcon()[$pallet->status->value],
            'items'                 => StoredItemResource::collection($this->storedItems ?? []),
        ];
    }
}
