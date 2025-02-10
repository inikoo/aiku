<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 12 Jul 2023 13:44:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use App\Http\Resources\HasSelfCall;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $slug
 * @property string $reference
 * @property \App\Enums\Fulfilment\StoredItem\StoredItemStateEnum $state
 * @property string $status
 * @property string $notes
 * @property mixed $total_quantity
 * @property mixed $name
 * @property mixed $number_pallets
 */
class StoredItemsInWarehouseResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {

        return [
            'id'                => $this->id,
            'reference'         => $this->reference,
            'slug'              => $this->slug,
            'state'             => $this->state,
            'state_icon'        => $this->state->stateIcon()[$this->state->value],
            'total_quantity'    => $this->total_quantity,
            'name'              => $this->name,
            'number_pallets'    => $this->number_pallets


        ];
    }
}
