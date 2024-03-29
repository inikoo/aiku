<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 03 Feb 2024 11:07:20 Malaysia Time, Bali Airport, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $reference
 * @property mixed $customer_reference
 * @property mixed $fulfilment_customer_id
 * @property mixed $slug
 * @property mixed $notes
 * @property mixed $state
 * @property mixed $location_slug
 * @property mixed $location_code
 * @property mixed $location_id
 * @property mixed $warehouse_id
 * @property mixed $pallet_delivery_id
 * @property mixed $pallet_return_id
 * @property mixed $fulfilment_customer_name
 * @property mixed $fulfilment_customer_slug
 */
class PalletsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                       => $this->id,
            'slug'                     => $this->slug,
            'reference'                => $this->reference,
            'customer_reference'       => (string)$this->customer_reference,
            'fulfilment_customer_name' => $this->fulfilment_customer_name,
            'fulfilment_customer_slug' => $this->fulfilment_customer_slug,
            'fulfilment_customer_id'   => $this->fulfilment_customer_id,
            'notes'                    => (string)$this->notes,
            'state'                    => $this->state,
            'location'                 => $this->location_slug,
            'location_code'            => $this->location_code,
            'location_id'              => $this->location_id,
            'state_label'              => $this->state->labels()[$this->state->value],
            'state_icon'               => $this->state->stateIcon()[$this->state->value],
            'stored_items'             => $this->storedItems->map(fn ($storedItem) => [
                'reference' => $storedItem->reference,
                'quantity'  => (int)$storedItem->pivot->quantity,
            ]),
            'stored_items_quantity'    => (int)$this->storedItems()->sum('quantity'),
            'updateRoute'              => match (request()->routeIs('retina.*')) {
                true => [
                    'name'       => 'retina.models.pallet.update',
                    'parameters' => $this->id
                ],
                default => [
                    'name'       => 'grp.models.pallet.update',
                    'parameters' => $this->id
                ]
            },
            'deleteRoute'              => match (request()->routeIs('retina.*')) {
                true => [
                    'name'       => 'retina.models.pallet.delete',
                    'parameters' => $this->id
                ],
                default => [
                    'name'       => 'grp.models.pallet.delete',
                    'parameters' => $this->id
                ]
            },
            'deleteFromDeliveryRoute'  => match (request()->routeIs('retina.*')) {
                true => [
                    'name'       => 'retina.models.pallet-delivery.pallet.delete',
                    'parameters' => [$this->pallet_delivery_id, $this->id]
                ],
                default => [
                    'name'       => 'grp.models.fulfilment-customer.pallet-delivery.pallet.delete',
                    'parameters' => [$this->fulfilment_customer_id, $this->pallet_delivery_id, $this->id]
                ]
            },
            'deleteFromReturnRoute'    => match (request()->routeIs('retina.*')) {
                true => [
                    'name'       => 'retina.models.pallet-return.pallet.delete',
                    'parameters' => [$this->pallet_return_id, $this->id]
                ],
                default => [
                    'name'       => 'grp.models.fulfilment-customer.pallet-return.pallet.delete',
                    'parameters' => [$this->fulfilment_customer_id, $this->pallet_return_id, $this->id]
                ]
            },
            'notReceivedRoute'         => [
                'name'       => 'grp.models.warehouse.pallet.not-received',
                'parameters' => [$this->warehouse_id, $this->id]
            ],
            'undoNotReceivedRoute'     => [
                'name'       => 'grp.models.warehouse.pallet.undo-not-received',
                'parameters' => [$this->warehouse_id, $this->id]
            ],
            'bookInRoute'              => [
                'name'       => 'grp.models.warehouse.pallet.booked-in',
                'parameters' => [$this->warehouse_id, $this->id]
            ],
            'updateLocationRoute'              => [
                'name'       => 'grp.models.warehouse.pallets.location.update',
                'parameters' => [$this->warehouse_id, $this->id]
            ],
            'storeStoredItemRoute'     => match (request()->routeIs('retina.*')) {
                true => [
                    'name'       => 'retina.models.pallet.stored-items.update',
                    'parameters' => [$this->id]
                ],
                default => [
                    'name'       => 'grp.models.pallet.stored-items.update',
                    'parameters' => [$this->id]
                ]
            },
        ];
    }
}
