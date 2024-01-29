<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 29 Jan 2024 10:08:24 Malaysia Time, Bali Office, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Fulfilment;

use App\Actions\Fulfilment\Fulfilment\Hydrators\FulfilmentHydrateWarehouses;
use App\Actions\Inventory\Warehouse\Hydrators\WarehouseHydrateFulfilments;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Inventory\Warehouse;
use Lorisleiva\Actions\Concerns\AsAction;

class AttachWarehouseToFulfilment
{
    use AsAction;


    public function handle(Fulfilment $fulfilment, Warehouse $warehouse): Fulfilment
    {
        $fulfilment->warehouses()->attach($warehouse);
        WarehouseHydrateFulfilments::run($warehouse);
        FulfilmentHydrateWarehouses::run($fulfilment);

        return $fulfilment;
    }


}
