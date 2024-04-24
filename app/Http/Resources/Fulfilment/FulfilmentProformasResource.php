<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jan 2024 17:29:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use App\Models\Fulfilment\FulfilmentProforma;
use Illuminate\Http\Resources\Json\JsonResource;

class FulfilmentProformasResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var FulfilmentProforma $proforma */
        $proforma = $this;

        return [
            'id'     => $proforma->id,
            'slug'   => $proforma->slug,
            'number' => $proforma->number
        ];
    }
}
