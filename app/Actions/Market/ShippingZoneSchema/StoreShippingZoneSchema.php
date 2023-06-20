<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 15:08:02 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Market\ShippingZoneSchema;

use App\Models\Market\ShippingZoneSchema;
use App\Models\Market\Shop;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreShippingZoneSchema
{
    use AsAction;
    use WithAttributes;

    public function handle(Shop $shop, array $modelData): ShippingZoneSchema
    {
        /** @var ShippingZoneSchema */
        return $shop->shippingZoneSchemas()->create($modelData);
    }

    public function rules(): array
    {
        return [
            'name'   => ['required', 'max:250', 'string'],
            'status' => ['sometimes', 'required', 'boolean'],
            'data'   => ['sometimes', 'required']
        ];
    }

    public function action(Shop $shop, array $objectData): ShippingZoneSchema
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($shop, $validatedData);
    }
}
