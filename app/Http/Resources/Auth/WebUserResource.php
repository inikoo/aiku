<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jul 2023 12:58:20 Malaysia Time, Sanur, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Auth;

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
