<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use Illuminate\Http\Resources\Json\JsonResource;

class PalletReturnsResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var \App\Models\Fulfilment\PalletReturn $palletReturn */
        $palletReturn = $this;
        // dd($palletReturn);
        return [
            'id'                    => $palletReturn->id,
            'created_at'            => $palletReturn->created_at,
            'slug'                  => $palletReturn->slug,
            'reference'             => $palletReturn->reference,
            'state'                 => $palletReturn->state,
            'state_label'           => $palletReturn->state->labels()[$palletReturn->state->value],
            'state_icon'            => $palletReturn->state->stateIcon()[$palletReturn->state->value],
            'type'                  => $palletReturn->type,
            'type_label'            => $palletReturn->type->labels()[$palletReturn->type->value],
            'type_icon'             => $palletReturn->type->stateIcon()[$palletReturn->type->value],
            'customer_reference'    => $palletReturn->customer_reference,
            'number_pallets'        => $palletReturn->stats->number_pallets,
            'number_services'       => $palletReturn->stats->number_services,
            'number_physical_goods' => $palletReturn->stats->number_physical_goods,
            'dispatched_at'         => $palletReturn->dispatched_at,
            'total_amount'          => $palletReturn->total_amount,
            'currency_code'         => $palletReturn->currency->code,
            'customer'              => $palletReturn->fulfilmentCustomer->customer,
        ];
    }
}
