<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use App\Models\Fulfilment\PalletDelivery;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $customer_name
 * @property string $customer_slug
 */
class PalletDeliveriesResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var PalletDelivery $palletDelivery */
        $palletDelivery = $this;

        return [
            'id'                 => $palletDelivery->id,
            'slug'               => $palletDelivery->slug,
            'reference'          => $palletDelivery->reference,
            'state'              => $palletDelivery->state,
            'state_label'        => $palletDelivery->state->labels()[$palletDelivery->state->value],
            'state_icon'         => $palletDelivery->state->stateIcon()[$palletDelivery->state->value],
            'customer_reference' => $palletDelivery->customer_reference,
            'number_pallets'     => $palletDelivery->number_pallets,
            'customer_name'      => $this->customer_name,
            'customer_slug'      => $this->customer_slug,
            'dispatched_date'    => $palletDelivery->dispatched_at
        ];
    }
}
