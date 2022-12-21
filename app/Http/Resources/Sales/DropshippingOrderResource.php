<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 19 Dec 2022 15:07:08 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Sales;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $number
 * @property string $customer_number
 */
class DropshippingOrderResource extends JsonResource
{

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'customer_number' => $this->customer_number,
        ];
    }
}
