<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 03 Feb 2024 11:07:20 Malaysia Time, Bali Airport, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use App\Models\Fulfilment\Pallet;
use Illuminate\Http\Resources\Json\JsonResource;

class PalletsResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Pallet $pallet */
        $pallet = $this;

        return [

            'id'                     => $pallet->id,
            'reference'              => $pallet->reference,
            'customer_reference'     => $pallet->customer_reference,
            'customer_name'          => $pallet->fulfilmentCustomer->customer->name,
            'fulfilment_customer_id' => $pallet->fulfilmentCustomer->id,
            'slug'                   => $pallet->slug,
            'notes'                  => $pallet->notes,
            'state'                  => $pallet->state,
            'location'               => $pallet->location?->slug,
            'location_id'            => $pallet->location?->id,
            'state_label'            => $pallet->state->labels()[$pallet->state->value],
            'state_icon'             => $pallet->state->stateIcon()[$pallet->state->value],
            'stored_items'           => $pallet->items,
            'updateRoute'            => match (request()->routeIs('retina.*')) {
                true => [
                    'name'       => 'retina.models.pallet.update',
                    'parameters' => $pallet->id
                ],
                default => [
                    'name'       => 'grp.models.pallet.update',
                    'parameters' => $pallet->id
                ]
            },
            'deleteRoute'            => match (request()->routeIs('retina.*')) {
                true => [
                    'name'       => 'retina.models.pallet.delete',
                    'parameters' => $pallet->id
                ],
                default => [
                    'name'       => 'grp.models.pallet.delete',
                    'parameters' => $pallet->id
                ]
            },
            'notReceivedRoute'       => [
                'name'       => 'grp.models.warehouse.pallet.not-received',
                'parameters' => [$pallet->warehouse_id, $pallet->id]
            ],
            'undoNotReceivedRoute'       => [
                'name'       => 'grp.models.warehouse.pallet.undo-not-received',
                'parameters' => [$pallet->warehouse_id, $pallet->id]
            ],
            'bookInRoute'            => [
                'name'       => 'grp.models.warehouse.pallet.booked-in',
                'parameters' => [$pallet->warehouse_id, $pallet->id]
            ],
            'storeStoredItemRoute'   => [
                'name'       => 'grp.models.pallet.stored-items.update',
                'parameters' => [$pallet->id]
            ],
        ];
    }
}
