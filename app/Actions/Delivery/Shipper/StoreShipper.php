<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 05 Dec 2021 02:07:56 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Delivery\Shipper;

use App\Actions\StoreModelAction;
use App\Models\Utils\ActionResult;
use App\Models\Delivery\Shipper;
use Lorisleiva\Actions\Concerns\AsAction;


class StoreShipper extends StoreModelAction
{
    use AsAction;

    public function handle(array $modelData): ActionResult
    {
        $shipper       = Shipper::create($modelData);
        return $this->finalise($shipper);
    }


}
