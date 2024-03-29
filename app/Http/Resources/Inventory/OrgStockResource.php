<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 22 Oct 2022 18:52:52 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Inventory;

use App\Models\Inventory\OrgStock;
use Illuminate\Http\Resources\Json\JsonResource;

class OrgStockResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var OrgStock $orgStock */
        $orgStock = $this;

        return [
            'slug'               => $orgStock->slug,
            'code'               => $orgStock->stock->code,
            'description'        => $orgStock->stock->description,
            'unit_value'         => $orgStock->stock->unit_value,
            'number_locations'   => $orgStock->stats->number_locations,
            'quantity_locations' => $orgStock->quantity_in_locations,
            'family_slug'        => $orgStock->orgStockFamily->slug,
            'family_code'        => $orgStock->orgStockFamily->stockFamily->code,
        ];
    }
}
