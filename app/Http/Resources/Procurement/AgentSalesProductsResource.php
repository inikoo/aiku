<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Resources\Json\JsonResource;

class AgentSalesProductsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'code'           => $this->code,
            'name'           => $this->name,
            'supplier_code'  => $this->supplier_code,
            'supplier_slug'  => $this->supplier_slug,
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
            'stock_value'    => round((float) $this->stock_value, 2),
            'organisations'  => collect(json_decode($this->organisations ?? '[]', true))->map(fn (array $organisation) => [
                ...$organisation,
                'sold'  => round((float) $organisation['sold'], 2),
                'stock' => round((float) $organisation['stock'], 2),
            ])->all(),
        ];
    }
}
