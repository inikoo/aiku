<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
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
class PalletResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'reference'     => $this->reference,
            'slug'          => $this->slug,
            'customer_name' => $this->customer['name'],
            'location'      => $this->location ? $this->location['slug'] : '-'
        ];
    }
}
