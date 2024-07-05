<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 05 Jul 2024 12:12:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $asset_id
 * @property mixed $asset_type*@property mixed $id
 * @property mixed $asset_slug
 * @property mixed $asset_code
 * @property mixed $asset_name
 * @property mixed $asset_price
 * @property mixed $asset_unit
 * @property mixed $asset_units
 * @property mixed $currency_code
 * @property mixed $quantity
 * @property mixed $id
 */
class FulfilmentTransactionResource extends JsonResource
{
    public function toArray($request): array
    {
        /*
        if ($this->asset_type == 'service') {
            $unitAbbreviation = $this->asset_unit ? $this->asset_unit->abbreviations()[$this->asset_unit->value] : 's';
            $unitLabel        = $this->asset_unit ? $this->asset_unit->labels()[$this->asset_unit->value] : __('service');
        } else {
            $unitAbbreviation = $this->asset_unit ? $this->asset_unit->abbreviations()[$this->asset_unit->value] : 'u';
            $unitLabel        = $this->asset_unit ? $this->asset_unit->labels()[$this->asset_unit->value] : __('unit');
        }
*/

        return [
            'id'                => $this->id,
            'asset_type'        => $this->asset_type,
            'asset_id'          => $this->asset_id,
            'asset_slug'        => $this->asset_slug,
            'asset_code'        => $this->asset_code,
            'asset_name'        => $this->asset_name,
            'asset_price'       => $this->asset_price,
            'asset_unit'        => $this->asset_unit,
            'asset_units'       => $this->asset_units,
            'currency_code'     => $this->currency_code,
        //    'unit_abbreviation' => $unitAbbreviation,
        //    'unit_label'        => $unitLabel,
            'quantity'          => $this->quantity,
            'total'             => 99


        ];
    }
}
