<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 15 Jan 2024 11:57:49 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Assets;

use App\Http\Resources\HasSelfCall;
use App\Models\Assets\Timezone;
use Illuminate\Http\Resources\Json\JsonResource;

class TimezoneResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        /** @var Timezone $timezone */
        $timezone = $this;

        return [
            'id'     => $timezone->id,
            'name'   => $timezone->name,
            'offset' => $timezone->offset,
        ];
    }

}
