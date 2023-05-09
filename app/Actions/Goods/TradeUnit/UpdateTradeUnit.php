<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 May 2023 11:42:32 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\TradeUnit;

use App\Actions\WithActionUpdate;
use App\Models\Goods\TradeUnit;

class UpdateTradeUnit
{
    use WithActionUpdate;

    public function handle(TradeUnit $tradeUnit, array $modelData): TradeUnit
    {
        return $this->update($tradeUnit, $modelData, ['data', 'dimensions']);
    }

    public function rules(): array
    {
        return [
            'code'         => ['required', 'unique:group.trade_units', 'between:2,9', 'alpha'],
            'name'         => ['required', 'max:250', 'string'],
            'description'  => ['sometimes', 'required'],
            'barcode'      => ['sometimes', 'required'],
            'gross_weight' => ['sometimes', 'required', 'numeric'],
            'net_weight'   => ['sometimes', 'required', 'numeric'],
            'dimensions'   => ['sometimes', 'required'],
            'type'         => ['sometimes', 'required'],
            'image_id'     => ['sometimes', 'required', 'exists:media,id'],
            'data'         => ['sometimes', 'required']
        ];
    }

    public function action(TradeUnit $tradeUnit, array $objectData): TradeUnit
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($tradeUnit, $validatedData);
    }
}
