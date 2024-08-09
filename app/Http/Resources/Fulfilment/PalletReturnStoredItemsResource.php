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
 * @property mixed $type
 * @property mixed $pivot
 * @property mixed $status
 * @property mixed $location_slug
 * @property mixed $location_code
 * @property mixed $location_id
 * @property mixed $warehouse_id
 * @property mixed $pallet_delivery_id
 * @property mixed $pallet_return_id
 * @property mixed $pallet_id
 * @property mixed $fulfilment_customer_name
 * @property mixed $fulfilment_customer_slug
 */
class PalletReturnStoredItemsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                                => $this->id,
            'slug'                              => $this->slug,
            'reference'                         => $this->reference,
            'state'                             => $this->state,
            'state_icon'                        => $this->state->stateIcon()[$this->state->value],
            'quantity'                          => (int) $this->quantity,
            'deleteRoute'                       => match (request()->routeIs('retina.*')) {
                true => [
                    'name'       => 'retina.models.pallet-return.stored-item.delete',
                    'parameters' => [$this->pallet_return_id, $this->pallet_id]
                ],
                default => [
                    'name'       => 'grp.models.fulfilment-customer.pallet-return.stored-item.delete',
                    'parameters' => [$this->fulfilment_customer_id, $this->pallet_return_id, $this->id]
                ]
            },
        ];
    }
}
