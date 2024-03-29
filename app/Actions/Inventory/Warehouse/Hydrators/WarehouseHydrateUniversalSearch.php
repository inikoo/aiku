<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Warehouse\Hydrators;

use App\Models\Inventory\Warehouse;
use Lorisleiva\Actions\Concerns\AsAction;

class WarehouseHydrateUniversalSearch
{
    use AsAction;


    public function handle(Warehouse $warehouse): void
    {
        $warehouse->universalSearch()->updateOrCreate(
            [],
            [
                'section'     => 'inventory',
                'title'       => trim($warehouse->name.' '.$warehouse->code),
                'description' => ''
            ]
        );
    }

}
