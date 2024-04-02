<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 02 Apr 2024 14:30:49 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\CRM;

use App\Http\Resources\HasSelfCall;
use App\Models\CRM\Customer;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $number_active_clients
 */
class CustomerResource extends JsonResource
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
            'number_active_clients' => $this->number_active_clients
        ];
    }
}
