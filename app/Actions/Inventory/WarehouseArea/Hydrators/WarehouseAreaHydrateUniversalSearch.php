<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\WarehouseArea\Hydrators;

use App\Actions\WithRoutes;
use App\Actions\WithTenantJob;
use App\Models\Inventory\WarehouseArea;
use Lorisleiva\Actions\Concerns\AsAction;

class WarehouseAreaHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;
    use WithRoutes;

    public function handle(WarehouseArea $warehouseArea): void
    {
        $warehouseArea->universalSearch()->create(
            [
                'section' => 'StockFamily',
                'route' => $this->routes(),
                'icon' => 'fa-map-signs',
                'primary_term'   => $warehouseArea->name,
                'secondary_term' => $warehouseArea->code
            ]
        );
    }

}
