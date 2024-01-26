<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jan 2024 17:29:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $reference
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * @property string $name
 * @property string $contact_name
 * @property string $company_name
 * @property string $phone
 * @property string $shop_code
 * @property string $shop_slug
 * @property string $slug
 * @property int $number_pallets
 */
class FulfilmentCustomersResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'slug'                          => $this->slug,
            'reference'                     => $this->reference,
            'name'                          => $this->name,
            'contact_name'                  => $this->contact_name,
            'company_name'                  => $this->company_name,
            'email'                         => $this->email,
            'phone'                         => $this->phone,
            'number_pallets_status_storing' => $this->number_pallets_status_storing,


        ];
    }
}
