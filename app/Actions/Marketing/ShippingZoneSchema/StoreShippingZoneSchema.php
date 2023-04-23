<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 15:08:02 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\ShippingZoneSchema;

use App\Models\Marketing\ShippingZoneSchema;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreShippingZoneSchema
{
    use AsAction;

    public function handle(ShippingZoneSchema $shippingZoneSchema, array $modelData): ShippingZoneSchema
    {
        return $shippingZoneSchema->create($modelData);
    }
}
