<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 22 Feb 2023 22:40:34 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Dispatching;

use Illuminate\Http\Resources\Json\JsonResource;

class PickingsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,
            'org_stock_code'      => $this->org_stock_code,
            'org_stock_name'      => $this->org_stock_name,
            'picker_name'         => $this->picker_name ?? null,
            'packer_name'         => $this->packer_name ?? null,
            'vessel_picking'      => $this->vessel_picking ?? null,
            'vessel_packing'      => $this->vessel_packing ?? null
        ];
    }
}
