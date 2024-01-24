<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 Jan 2024 14:58:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use App\Models\Fulfilment\Fulfilment;
use Illuminate\Http\Resources\Json\JsonResource;

class FulfilmentResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Fulfilment $fulfilment */
        $fulfilment=$this;
        return [
            'id'      => $fulfilment->id,
            'slug'    => $fulfilment->slug,
            'code'    => $fulfilment->shop->code,
            'name'    => $fulfilment->shop->name,
            'state'   => $fulfilment->shop->state,
        ];
    }
}
