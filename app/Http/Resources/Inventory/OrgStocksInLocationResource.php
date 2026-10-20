<?php

/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 22 Oct 2022 18:52:52 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Inventory;

use App\Http\Resources\HasSelfCall;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $code
 * @property mixed $name
 * @property mixed $slug
 * @property mixed $sku_value
 * @property mixed $type
 * @property mixed $picking_priority
 * @property mixed $value
 * @property mixed $dropshipping_pipe
 * @property mixed $quantity
 * @property mixed $notes
 * @property mixed $location_org_stock_id
 * @property mixed $packed_at
 * @property mixed $packed_quantity
 * @property mixed $packed_by
 * @property string|null $un_numbers
 */
class OrgStocksInLocationResource extends JsonResource
{
    use HasSelfCall;

    public static $wrap = null;

    public function toArray($request): array
    {
        return [
            'id'                    => $this->id,
            'code'                  => $this->code,
            'name'                  => $this->name,
            'slug'                  => $this->slug,
            'sku_value'             => $this->sku_value,
            'type'                  => $this->type,
            'picking_priority'      => $this->picking_priority,
            'value'                 => $this->value,
            'dropshipping_pipe'     => $this->dropshipping_pipe,
            'quantity'              => trimDecimalZeros($this->quantity),
            'notes'                 => $this->notes,
            'location_org_stock_id' => $this->location_org_stock_id,
            'packed_at'             => $this->packed_at,
            'packed_by'             => $this->packed_by,
            'packed_quantity'       => $this->packed_at ? trimDecimalZeros($this->packed_quantity) : null,
            'un_numbers'            => $this->un_numbers ? array_values(json_decode($this->un_numbers, true)) : [],
            'is_packed'             => $this->packed_at && (float) $this->packed_quantity == (float) $this->quantity,
        ];
    }
}
