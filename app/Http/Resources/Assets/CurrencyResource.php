<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 08 May 2024 22:38:21 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Assets;

use App\Http\Resources\HasSelfCall;
use App\Models\Assets\Currency;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        /** @var Currency $currency */
        $currency = $this;

        return [
            'id'     => $currency->id,
            'code'   => $currency->code,
            'name'   => $currency->name,
            'symbol' => $currency->symbol
        ];
    }

}
