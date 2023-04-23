<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 15:08:02 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\ShippingZone;

use App\Models\Marketing\ShippingZone;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreShippingZone
{
    use AsAction;

    public function handle(ShippingZone $shippingZone, array $modelData): ShippingZone
    {
        return $shippingZone->create($modelData);
    }
}
