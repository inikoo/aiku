<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 19 Dec 2022 15:07:08 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Dropshipping;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $slug
 * @property string $reference
 * @property string $name
 * @property string $contact_name
 * @property string $company_name
 * @property string $email
 * @property string $phone
 * @property string $created_at
 * @property string $updated_at
 */
class CustomerClientResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'slug'                  => $this->slug,
            'reference'             => $this->reference,
            'name'                  => $this->name,
            'contact_name'          => $this->contact_name,
            'company_name'          => $this->company_name,
            'email'                 => $this->email,
            'phone'                 => $this->phone,
            'created_at'            => $this->created_at,
            'updated_at'            => $this->updated_at,
        ];
    }
}
