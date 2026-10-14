<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Resources\Json\JsonResource;

class AgentSalesSuppliersResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'slug'           => $this->slug,
            'code'           => $this->code,
            'name'           => $this->name,
            'products'       => (int) $this->products,
            'selling'        => (int) $this->selling,
            'sold'           => round((float) $this->sold, 2),
            'this_year'      => round((float) $this->this_year, 2),
            'last_year'      => round((float) $this->last_year, 2),
            'change_percent' => $this->change_percent === null ? null : round((float) $this->change_percent, 1),
            'rating'         => $this->rating === null ? null : round((float) $this->rating, 1),
            'reviews'        => (int) $this->reviews,
            'refund_percent' => $this->refund_percent === null ? null : round((float) $this->refund_percent, 1),
            'faults'         => (int) $this->faults,
            'short_percent'  => $this->short_percent === null ? null : round((float) $this->short_percent, 1),
            'short_lines'    => (int) $this->short_lines,
            'out_of_stock'   => (int) $this->out_of_stock,
            'low'            => (int) $this->low,
            'dead'           => (int) $this->dead,
        ];
    }
}
