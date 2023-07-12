<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\StockFamily\Hydrators;

use App\Actions\Traits\WithTenantJob;
use App\Models\Inventory\StockFamily;
use Lorisleiva\Actions\Concerns\AsAction;

class StockFamilyHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;

    public function handle(StockFamily $stockFamily): void
    {
        $stockFamily->universalSearch()->create(
            [
                'section' => 'StockFamily',
                'route'   => json_encode([
                    'name'      => 'inventory.stock-families.show',
                    'arguments' => [
                        $stockFamily->slug
                    ]
                ]),
                'icon'           => 'fa-boxes-alt',
                'primary_term'   => $stockFamily->name,
                'secondary_term' => $stockFamily->code
            ]
        );
    }

}
