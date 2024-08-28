<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 28 Aug 2024 11:12:11 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Inventory;

use App\Models\Inventory\Location;
use App\Models\Inventory\LocationOrgStock;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $pivot
 * @property int $id
 */
class OrgStockLocationsResource extends JsonResource
{
    public static $wrap = null;

    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'quantity'         => $this->pivot->quantity,
            'value'            => $this->pivot->value,
            'audited_at'       => $this->pivot->audited_at,
            'commercial_value' => $this->pivot->commercial_value,
            'type'             => $this->pivot->type,
            'picking_priority' => $this->pivot->picking_priority,
            'notes'            => $this->pivot->notes,
            'data'             => $this->pivot->data,
            'settings'         => $this->pivot->settings,
            'created_at'       => $this->pivot->created_at,
            'updated_at'       => $this->pivot->updated_at,
            'location' => new LocationsResource($this->pivot->location)


        ];
    }
}
