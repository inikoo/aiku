<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 May 2023 22:20:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Assets;

use App\Http\Resources\HasSelfCall;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 *
 * @property string $code
 * @property string $iso3
 * @property string $name
 *
 */
class CountryResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        return [

            'code' => $this->code,
            'iso3' => $this->iso3,
            'name' => $this->name,
        ];
    }

}
