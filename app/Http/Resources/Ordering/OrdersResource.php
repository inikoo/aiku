<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 09:40:37 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Ordering;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $slug
 * @property string $code
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property string $name
 * @property string $state
 * @property string $shop_slug
 * @property string $date
 * @property string $reference
 *
 */
class OrdersResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'slug'              => $this->slug,
            'reference'         => $this->reference,
            'date'              => $this->date,
            'name'              => $this->name,
            'state'             => $this->state,
            'total_amount'      => $this->total_amount,
            'customer_name'     => $this->customer_name,
            'customer_slug'     => $this->customer_slug,
            'payment_state'     => $this->payment_state,
            'payment_status'    => $this->payment_status,
            'currency_code'     => $this->currency_code,
            'currency_id'       => $this->currency_id,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'shop_slug'         => $this->shop_slug,
        ];
    }
}
