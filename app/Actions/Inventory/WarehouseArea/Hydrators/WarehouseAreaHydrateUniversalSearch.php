<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\WarehouseArea\Hydrators;

use App\Models\Inventory\WarehouseArea;
use Lorisleiva\Actions\Concerns\AsAction;

class WarehouseAreaHydrateUniversalSearch
{
    use AsAction;


    public function handle(WarehouseArea $warehouseArea): void
    {
        $warehouseArea->universalSearch()->updateOrCreate(
            [],
            [
                'section' => 'inventory',
                'title'   => trim($warehouseArea->code.' '.$warehouseArea->name),
            ]
        );
    }

}
