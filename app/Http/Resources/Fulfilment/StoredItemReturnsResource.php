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
 * @property string $state
 * @property string $status
 * @property string $notes
 * @property \App\Models\CRM\Customer $customer
 * @property \App\Models\Inventory\Location $location
 */
class StoredItemReturnsResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        /** @var \App\Models\Fulfilment\StoredItemReturn $storedItemReturn */
        $storedItemReturn = $this;

        return [
            'slug'               => $storedItemReturn->slug,
            'reference'          => $storedItemReturn->reference,
            'state'              => $storedItemReturn->state,
            'state_label'        => $storedItemReturn->state->labels()[$storedItemReturn->state->value],
            'state_icon'         => $storedItemReturn->state->stateIcon()[$storedItemReturn->state->value],
            'customer_reference' => $storedItemReturn->customer_reference,
            'items'              => $storedItemReturn->items()->count(),
        ];
    }
}
