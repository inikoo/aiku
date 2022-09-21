<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 05 Dec 2021 02:08:15 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Delivery\Shipper;

use App\Actions\WithActionUpdate;
use App\Models\Delivery\Shipper;

class UpdateShipper
{
    use WithActionUpdate;

    public function handle(Shipper $shipper, array $modelData): Shipper
    {
        return $this->update($shipper, $modelData, ['data']);
    }
}
