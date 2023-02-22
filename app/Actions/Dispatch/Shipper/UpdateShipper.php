<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\Shipper;

use App\Actions\WithActionUpdate;
use App\Models\Dispatch\Shipper;

class UpdateShipper
{
    use WithActionUpdate;

    public function handle(Shipper $shipper, array $modelData): Shipper
    {
        return $this->update($shipper, $modelData, ['data']);
    }
}
