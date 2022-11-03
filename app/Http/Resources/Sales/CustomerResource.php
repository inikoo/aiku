<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 22 Oct 2022 18:53:15 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Sales;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $reference
 * @property mixed $email
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property mixed $name
 * @property mixed $contact_name
 * @property mixed $company_name
 * @property mixed $phone
 */
class CustomerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'reference'    => $this->reference,
            'name'         => $this->name,
            'contact_name' => $this->contact_name,
            'company_name' => $this->company_name,
            'email'        => $this->email,
            'phone'        => $this->phone,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,

        ];
    }
}
