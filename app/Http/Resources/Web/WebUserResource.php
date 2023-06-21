<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 27 Oct 2022 22:06:42 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Web;

use App\Http\Resources\Sales\CustomerResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $email
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property \App\Models\CRM\Customer $customer
 */
class WebUserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'email'      => $this->email,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'customer'   => CustomerResource::make($this->customer),

        ];
    }
}
