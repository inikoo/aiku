<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 03 Sept 2022 02:51:55 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\TradeUnit;

use App\Models\Marketing\TradeUnit;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreTradeUnit
{
    use AsAction;
    use WithAttributes;

    public function handle($modelData): TradeUnit
    {
        return TradeUnit::create($modelData);
    }

    public function rules(): array
    {
        return [
            'code'        => ['required', 'unique:tenant.trade_units', 'between:2,9', 'alpha'],
            'name'        => ['required', 'max:250', 'string'],
            'description' => ['sometimes', 'required'],
            'barcode'     => ['sometimes', 'required'],
            'weight'      => ['sometimes', 'required', 'numeric'],
            'dimensions'  => ['sometimes', 'required'],
            'type'        => ['sometimes', 'required'],
            'image_id'    => ['sometimes', 'required', 'exists:media,id'],
            'data'        => ['sometimes', 'required']
        ];
    }

    public function action(array $objectData): TradeUnit
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($validatedData);
    }
}
