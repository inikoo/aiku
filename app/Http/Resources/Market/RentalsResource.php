<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 18 Apr 2024 09:27:56 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Market;

use App\Models\Fulfilment\Rental;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $code
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string $main_outerable_price
 * @property string $description
 * @property mixed $type
 * @property mixed $auto_assign_asset
 * @property mixed $auto_assign_asset_type
 * @property mixed $price
 * @property mixed $currency_code
 * @property mixed $unit
 * @property mixed $state
 */
class RentalsResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Rental $rental */
        $rental=$this;

        return [
            'id'                     => $this->id,
            'code'                   => $this->code,
            'name'                   => $this->name,
            'price'                  => $this->price,
            'currency_code'          => $this->currency_code,
            'unit'                   => $rental->unit,
            'unit_abbreviation'      => $rental->unit->abbreviations()[$this->unit->value],
            'unit_label'             => $rental->unit->labels()[$this->unit->value],
            'description'            => $this->description,
            'auto_assign_asset_type' => $this->auto_assign_asset_type,
            'auto_assign_asset'      => $this->auto_assign_asset,
            'state_label'            => $this->state->labels()[$this->state->value],
            'state_icon'             => $this->state->stateIcon()[$this->state->value],
        ];
    }
}
