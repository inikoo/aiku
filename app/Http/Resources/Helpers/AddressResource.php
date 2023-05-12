<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 12 May 2023 13:27:22 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Helpers;

use App\Http\Resources\HasSelfCall;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 *
 * @property string $address_line_1
 * @property string $address_line_2
 * @property string $sorting_code
 * @property string $postal_code
 * @property string $locality
 * @property string $dependant_locality
 * @property string $administrative_area
 * @property string $country_code
 * @property integer $country_id
 * @property string $checksum
 * @property string $created_at
 * @property string $updated_at
 *
 */
class AddressResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        return [

            'country_id'            => $this->country_id,
            'address_line_1'        => $this->address_line_1,
            'address_line_2'        => $this->address_line_2,
            'sorting_code'          => $this->sorting_code,
            'postal_code'           => $this->postal_code,
            'locality'              => $this->locality,
            'dependant_locality'    => $this->dependant_locality,
            'administrative_area'   => $this->administrative_area,
            'country_code'          => $this->country_code,
            'checksum'              => $this->checksum,
            'created_at'            => $this->created_at,
            'updated_at'            => $this->updated_at,
        ];
    }

}
