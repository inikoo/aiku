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
        $pallet=$this;

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
            'state_label'            => $pallet->state->labels()[$pallet->state->value],
            'state_icon'             => $pallet->state->stateIcon()[$pallet->state->value],
            'updateRoute'            => [
                'name'   => 'grp.models.fulfilment-customer.pallet-delivery.pallet.update',
                'params' => [
                    'organisation'        => $pallet->fulfilmentCustomer->fulfilment->organisation->slug,
                    'fulfilmentCustomer'  => $pallet->fulfilmentCustomer->id,
                    'palletDelivery'      => $pallet->palletDelivery->slug,
                    'pallet'              => $pallet->id
                ]
            ],
            'deleteRoute'        => [
                'name'   => 'grp.models.fulfilment-customer.pallet-delivery.pallet.delete',
                'params' => [
                    'organisation'        => $pallet->fulfilmentCustomer->fulfilment->organisation->slug,
                    'fulfilmentCustomer'  => $pallet->fulfilmentCustomer->id,
                    'palletDelivery'      => $pallet->palletDelivery->slug,
                    'pallet'              => $pallet->id
                ]
            ],
        ];
    }
}
