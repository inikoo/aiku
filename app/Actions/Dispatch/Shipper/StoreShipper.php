<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\Shipper;

use App\Models\Dispatch\Shipper;
use Lorisleiva\Actions\Concerns\AsAction;


class StoreShipper
{
    use AsAction;

    public function handle(array $modelData): Shipper
    {
        return Shipper::create($modelData);
    }


}
