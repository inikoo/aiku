<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 03 Feb 2024 11:07:20 Malaysia Time, Bali Airport, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use App\Models\Fulfilment\StoredItem;
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
class EditStoredItemDeltasResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                               => $this->id,
            'slug'                             => $this->slug,
            'reference'                        => $this->reference,
            'customer_reference'               => (string)$this->customer_reference,


            'location_slug'                    => $this->location_slug,
            'location_code'                    => $this->location_code,
            'location_id'                      => $this->location_id,
            'audited_at'                       => $this->audited_at,

            'stored_items'                     => $this->storedItems->map(fn (StoredItem $storedItem) => [
                'id'             => $storedItem->id,
                'reference'      => $storedItem->reference,
                'notes'          => $storedItem->notes,
                'state'          => $storedItem->state,
                'state_icon'     => $storedItem->state->stateIcon()[$storedItem->state->value],
                'quantity'       => (int)$storedItem->pivot->quantity,
            ]),



            'auditRoute' => match (request()->routeIs('retina.*')) {
                true => [
                    'name'       => 'retina.models.pallet.stored-items.audit',
                    'parameters' => [$this->id]
                ],
                default => [
                    'name'       => 'grp.models.pallet.stored-items.audit',
                    'parameters' => [$this->id]
                ]
            },
            'resetAuditRoute' => [
                'name'       => 'grp.models.pallet.stored-items.audit.reset',
                'parameters' => [$this->id]
            ],
            'storeStoredItemRoute' => match (request()->routeIs('retina.*')) {
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
