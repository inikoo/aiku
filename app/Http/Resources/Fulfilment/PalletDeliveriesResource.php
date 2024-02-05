<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use Illuminate\Http\Resources\Json\JsonResource;

class PalletDeliveriesResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var \App\Models\Fulfilment\PalletDelivery $palletDelivery */
        $palletDelivery = $this;

        return [
            'slug'               => $palletDelivery->slug,
            'reference'          => $palletDelivery->reference,
            'state'              => $palletDelivery->state,
            'state_label'        => $palletDelivery->state->labels()[$palletDelivery->state->value],
            'state_icon'         => $palletDelivery->state->stateIcon()[$palletDelivery->state->value],
            'pallets'            => $palletDelivery->number_pallets,
            'customer_reference' => $palletDelivery->customer_reference,
            'number_pallets'     => $palletDelivery->number_pallets
        ];
    }
}
