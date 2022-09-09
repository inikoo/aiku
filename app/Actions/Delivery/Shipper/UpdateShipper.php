<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 05 Dec 2021 02:08:15 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Delivery\Shipper;

use App\Actions\UpdateModelAction;
use App\Models\Utils\ActionResult;
use App\Models\Delivery\Shipper;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateShipper extends UpdateModelAction
{
    use AsAction;

    public function handle( Shipper $shipper, array $modelData): ActionResult {

        $this->model=$shipper;
        $this->modelData=$modelData;
        return $this->updateAndFinalise(jsonFields:['data']);

    }
}
