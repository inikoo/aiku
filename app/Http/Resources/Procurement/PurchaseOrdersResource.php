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
        return [
            'number'       => $this->number,
            'slug'         => $this->slug,
            'date'         => $this->date,
            'parent_name'  => $this->parent_name,
        ];
    }

}
