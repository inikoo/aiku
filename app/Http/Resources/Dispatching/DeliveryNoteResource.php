<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 22 Feb 2023 22:40:34 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Dispatching;

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
class DeliveryNoteResource extends JsonResource
{
    public static $wrap = null;

    public function toArray($request): array
    {
        return [
            'slug'           => $this->slug,
            'reference'      => $this->reference,
            'date'           => $this->date,
            'state'          => $this->state,
            'type'           => $this->type,
            'status'         => $this->status,
            'weight'         => $this->weight,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
            'shop_slug'      => $this->shop_slug,
            'customer_slug'  => $this->customer_slug,
            'customer_name'  => $this->customer_name,
            'number_items'   => $this->number_items,
        ];
    }
}
