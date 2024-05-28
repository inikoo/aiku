<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use App\Http\Resources\Inventory\LocationResource;
use App\Models\Fulfilment\Pallet;
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
class PalletResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Pallet $pallet */
        $pallet=$this;
        
        return [
            'id'                    => $this->id,
            'reference'             => $pallet->reference,
            'customer_reference'    => $pallet->customer_reference,
            'slug'                  => $pallet->slug,
            'customer'              => [
                'name'                 => $this->fulfilmentCustomer->customer->name,
                'contact_name'         => $this->fulfilmentCustomer->customer->contact_name,
                'route'                => [
                    'name'       => 'grp.org.fulfilments.show.crm.customers.show',
                    'parameters' => [$pallet->organisation->slug, $pallet->fulfilment->slug, $pallet->fulfilmentCustomer->slug]
                                    ]
                ],
           
            'location'              => [
                                        'resource' => LocationResource::make($this->location),
                                        'route' => $request->user()->hasPermissionTo("locations.{$pallet->warehouse_id}.view")? [
                                            'name' => 'grp.org.warehouses.show.fulfilment.locations.show',
                                            'parameters' => [$pallet->organisation->slug, $pallet->warehouse->slug, $pallet->location->slug]
                                            ] : null
                                        ],
            'state'                 => $this->state,
            'status'                => $this->status,
            'notes'                 => $this->notes,
            'rental_id'             => $this->rental_id,
            'status_label'          => $pallet->status->labels()[$pallet->status->value],
            'status_icon'           => $pallet->status->statusIcon()[$pallet->status->value],
            'items'                 => StoredItemResource::collection($this->storedItems ?? [])
        ];
    }
}
