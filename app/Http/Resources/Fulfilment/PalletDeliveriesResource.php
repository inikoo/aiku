<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $customer_name
 * @property string $customer_slug
 * @property mixed $id
 * @property mixed $slug
 * @property mixed $reference
 * @property mixed $customer_reference
 * @property mixed $number_pallets
 * @property mixed $estimated_delivery_date
 * @property mixed $state
 */
class PalletDeliveriesResource extends JsonResource
{
    public function toArray($request): array
    {

        return [
            'id'                         => $this->id,
            'slug'                       => $this->slug,
            'reference'                  => $this->reference,
            'state'                      => $this->state,
            'state_label'                => $this->state->labels()[$this->state->value],
            'state_icon'                 => $this->state->stateIcon()[$this->state->value],
            'customer_reference'         => $this->customer_reference,
            'number_pallets'             => $this->number_pallets,
            'customer_name'              => $this->customer_name,
            'customer_slug'              => $this->customer_slug,
            'estimated_delivery_date'    => $this->estimated_delivery_date,

            'receiveRoute'               => [
                'name' => 'grp.models.pallet-delivery.received',
                'parameters' => $this->id
            ]
        ];
    }
}
