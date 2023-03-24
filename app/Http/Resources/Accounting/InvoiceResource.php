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
 *
 */
class InvoiceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'slug'       => $this->slug,
            'number'     => $this->number,
            'total'      => $this->total,
            'net'        => $this->net,
            'state'      => $this->state,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'shop_slug'  => $this->shop_slug
        ];
    }
}
