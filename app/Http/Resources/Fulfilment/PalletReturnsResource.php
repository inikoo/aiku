<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $slug
 * @property mixed $reference
 * @property mixed $state
 * @property mixed $type
 * @property mixed $customer_reference
 * @property mixed $number_pallets
 * @property mixed $number_services
 * @property mixed $number_physical_goods
 * @property mixed $total_amount
 * @property mixed $currency_code
 * @property mixed $date
 */
class PalletReturnsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                    => $this->id,
            'slug'                  => $this->slug,
            'reference'             => $this->reference,
            'state'                 => $this->state,
            'state_label'           => $this->state->labels()[$this->state->value],
            'state_icon'            => $this->state->stateIcon()[$this->state->value],
            'type'                  => $this->type,
            'type_label'            => $this->type->labels()[$this->type->value],
            'type_icon'             => $this->type->stateIcon()[$this->type->value],
            'customer_reference'    => $this->customer_reference,
            'number_pallets'        => $this->number_pallets,
            'number_services'       => $this->number_services,
            'number_physical_goods' => $this->number_physical_goods,
            'date'                  => $this->date,
            'total_amount'          => $this->total_amount,
            'currency_code'         => $this->currency_code,
        ];
    }
}
