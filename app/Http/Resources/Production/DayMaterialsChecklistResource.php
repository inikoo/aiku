<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Production;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Quantities in the material's units; the warehouse keeps SKOs of packed_in units each.
 *
 * @property int $id
 * @property string $code
 * @property string|null $description
 * @property string|null $unit
 * @property int|null $org_stock_id
 * @property string|null $org_stock_code
 * @property numeric $needed_units
 * @property numeric $at_factory_units
 * @property numeric $requested_units
 * @property numeric $warehouse_skos
 * @property numeric $box_units
 * @property numeric $packed_in
 * @property string $job_orders
 */
class DayMaterialsChecklistResource extends JsonResource
{
    public function toArray($request): array
    {
        $needed         = (float) $this->needed_units;
        $atFactory      = (float) $this->at_factory_units;
        $requested      = (float) $this->requested_units;
        $missingGuess   = round(max(0, $needed - $atFactory - $requested), 3);
        $warehouseUnits = round((float) $this->warehouse_skos * (float) $this->packed_in, 3);

        return [
            'id'                => $this->id,
            'code'              => $this->code,
            'description'       => $this->description,
            'unit'              => $this->unit,
            'org_stock_code'    => $this->org_stock_code,
            'can_be_requested'  => (bool) $this->org_stock_id,
            'needed_units'      => $needed,
            'at_factory_units'  => $atFactory,
            'requested_units'   => $requested,
            'suggested_missing' => $missingGuess,
            'warehouse_units'   => $warehouseUnits,
            'warehouse_short'   => $missingGuess > 0 && $warehouseUnits < $missingGuess,
            'box_units'         => (float) $this->box_units,
            'job_orders'        => $this->job_orders,
        ];
    }
}
