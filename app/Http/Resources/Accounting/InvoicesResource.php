<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:38:56 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Accounting;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $slug
 * @property string $code
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property string $name
 * @property string $state
 * @property string $shop_slug
 * @property string $number
 * @property string $total
 * @property string $net
 * @property mixed $date
 * @property mixed $type
 * @property mixed $shop_code
 * @property mixed $shop_name
 * @property mixed $customer_name
 * @property mixed $customer_slug
 * @property mixed $currency_code
 * @property mixed $currency_symbol
 *
 */
class InvoicesResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'slug'            => $this->slug,
            'number'          => $this->number,
            'total'           => $this->total,
            'net'             => $this->net,
            'state'           => $this->state,
            'date'            => $this->date,
            'type'            => $this->type,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
            'shop_slug'       => $this->shop_slug,
            'shop_code'       => $this->shop_code,
            'shop_name'       => $this->shop_name,
            'customer_name'   => $this->customer_name,
            'customer_slug'   => $this->customer_slug,
            'currency_code'   => $this->currency_code,
            'currency_symbol' => $this->currency_symbol,
        ];
    }
}
