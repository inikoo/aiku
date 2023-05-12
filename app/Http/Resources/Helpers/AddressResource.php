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
 * @property integer $country_id
 *
 */
class AddressResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        return [

            'country_id' => $this->country_id,
        ];
    }

}
