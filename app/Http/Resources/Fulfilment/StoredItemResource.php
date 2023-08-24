<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 12 Jul 2023 13:44:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

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
class StoredItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'reference'     => $this->reference,
            'slug'          => $this->slug,
            'customer_name' => $this->customer['name'],
            'location'      => $this->location ? $this->location['slug'] : '-',
            'state'         => $this->state,
            'notes'         => $this->notes ?? '-',
            'status'        => $this->status
        ];
    }
}
