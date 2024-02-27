<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 22 Oct 2022 18:53:15 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Sales;

use App\Http\Resources\HasSelfCall;
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
 * @property int $number_active_clients
 */
class CustomerResource extends JsonResource
{
    use HasSelfCall;
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
            'shop'                  => $this->shop_code,
            'shop_slug'             => $this->shop_slug,
            'number_active_clients' => $this->number_active_clients,


        ];
    }
}
