<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 25 May 2023 15:03:06 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\WarehouseArea\UI;

use App\Models\Inventory\WarehouseArea;
use Lorisleiva\Actions\Concerns\AsObject;

class GetWarehouseAreaShowcase
{
    use AsObject;

    /** @noinspection PhpUnusedParameterInspection */
    public function handle(WarehouseArea $warehouseArea): array
    {
        return [
            [

            ]
        ];
    }
}
