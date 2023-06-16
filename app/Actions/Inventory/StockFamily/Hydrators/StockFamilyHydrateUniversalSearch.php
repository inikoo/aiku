<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\StockFamily\Hydrators;

use App\Actions\WithRoutes;
use App\Actions\WithTenantJob;
use App\Models\Inventory\StockFamily;
use Lorisleiva\Actions\Concerns\AsAction;

class StockFamilyHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;
    use WithRoutes;

    public function handle(StockFamily $stockFamily): void
    {
        $stockFamily->universalSearch()->create(
            [
                'section' => 'StockFamily',
                'route' => $this->routes(),
                'icon' => 'fa-boxes-alt',
                'primary_term'   => $stockFamily->name,
                'secondary_term' => $stockFamily->code
            ]
        );
    }

}
