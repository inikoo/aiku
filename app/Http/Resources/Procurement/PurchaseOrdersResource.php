<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 11:30:19 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $number
 * @property string $slug
 * @property string $date
 * @property mixed $parent_name
 */
class PurchaseOrdersResource extends JsonResource
{
    public function toArray($request): array
    {
        // dd($this);
        return [
            'reference'         => $this->reference,
            'state'             => $this->state,
            'state_icon'        => $this->state->stateIcon()[$this->state->value],
            'amount'            => $this->cost_total,
            'parent_type'       => $this->parent_type,
            'parent_name'       => $this->parent_name,
            'parent_slug'       => $this->parent->slug,
            'slug'              => $this->slug,
            'date'              => $this->date,
            'currency_code'     => $this->currency->code,
        ];
    }

}
