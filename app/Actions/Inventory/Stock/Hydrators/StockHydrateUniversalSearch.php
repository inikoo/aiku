<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Stock\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Inventory\Stock;
use Lorisleiva\Actions\Concerns\AsAction;

class StockHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;

    public function handle(Stock $stock): void
    {
        $stock->universalSearch()->create(
            [
                'primary_term'   => $stock->quantity_in_locations.' '.$stock->code,
                'secondary_term' => $stock->units_per_pack.' '.$stock->units_per_carton
            ]
        );
    }

}
