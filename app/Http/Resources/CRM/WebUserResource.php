<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:46:43 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\CRM;

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
