<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 22 Oct 2022 18:53:15 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Sales;

use App\Http\Resources\HasSelfCall;
use App\Models\CRM\Customer;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $number_active_clients
 */
class CustomersResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        /** @var Customer $customer */
        $customer = $this;

        return [
            'slug'                  => $customer->slug,
            'reference'             => $customer->reference,
            'name'                  => $customer->name,
            'contact_name'          => $customer->contact_name,
            'company_name'          => $customer->company_name,
            'location'              => $customer->location,
            'email'                 => $customer->email,
            'phone'                 => $customer->phone,
            'created_at'            => $customer->created_at,
            'number_active_clients' => $this->number_active_clients,


        ];
    }
}
