<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 30 Oct 2022 01:22:24 Greenwich Mean Time, Plane HK->KL
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Sales;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $name
 * @property string $reference
 * @property string $shop_code
 * @property int $shop_id
 */
class InertiaTableCustomerResource extends JsonResource
{

    public function toArray($request): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'reference' => $this->reference,
            'shop'      => $this->shop_code,
            'shop_id'   => $this->shop_id,

        ];
    }
}
