<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jan 2024 17:29:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use Illuminate\Http\Resources\Json\JsonResource;

/**
*
 * @property int $id
 * @property int $reference
 * @property int $slug
 */
class RecurringBillsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                       => $this->id,
            'slug'                     => $this->slug,
            'reference'                => $this->reference,
            'customer_name'            => $this->customer_name,
            'fulfilment_customer_slug' => $this->fulfilment_customer_slug,
            'net_amount'               => $this->net_amount,
            'start_date'               => $this->start_date,
            'end_date'                 => $this->end_date,
            'currency_code'            => $this->currency_code,
            'currency_symbol'          => $this->currency_symbol,
        ];
    }
}
