<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 15:08:02 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ShippingZone;

use App\Models\Catalogue\ShippingZone;
use App\Models\Catalogue\ShippingZoneSchema;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreShippingZone
{
    use AsAction;
    use WithAttributes;

    public function handle(ShippingZoneSchema $shippingZoneSchema, array $modelData): ShippingZone
    {
        $modelData['shop_id'] = $shippingZoneSchema->shop_id;
        /** @var ShippingZone */
        return $shippingZoneSchema->shippingZone()->create($modelData);
    }

    public function rules(): array
    {
        return [
            'code'   => ['required', 'unique:shipping_zones', 'between:2,9', 'alpha'],
            'name'   => ['required', 'max:250', 'string'],
            'status' => ['sometimes', 'required', 'boolean']
        ];
    }

    public function action(ShippingZoneSchema $shippingZoneSchema, array $modelData): ShippingZone
    {
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($shippingZoneSchema, $validatedData);
    }
}
