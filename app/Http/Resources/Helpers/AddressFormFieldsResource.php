<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Jun 2023 13:57:50 Malaysia Time, Kuala Lumpur, Malaysia
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
 * @property string $country_code
 * @property integer $country_id
 * @property string $locality
 * @property string $dependant_locality
 * @property string $administrative_area

 * @property mixed $country
 *
 */
class AddressFormFieldsResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        return [
            'address_line_1'      => $this->address_line_1,
            'address_line_2'      => $this->address_line_2,
            'sorting_code'        => $this->sorting_code,
            'postal_code'         => $this->postal_code,
            'locality'            => $this->locality,
            'dependant_locality'  => $this->dependant_locality,
            'administrative_area' => $this->administrative_area,
            'country_code'        => $this->country_code,
            'country_id'          => $this->country_id,
        ];
    }

}
