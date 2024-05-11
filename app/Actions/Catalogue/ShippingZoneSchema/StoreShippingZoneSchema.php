<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 15:08:02 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ShippingZoneSchema;

use App\Models\Catalogue\ShippingZoneSchema;
use App\Models\Catalogue\Shop;
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

    public function action(Shop $shop, array $modelData): ShippingZoneSchema
    {
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($shop, $validatedData);
    }
}
