<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 22 Jan 2024 13:06:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\StockFamily\Hydrators;

use App\Models\SupplyChain\StockFamily;
use Lorisleiva\Actions\Concerns\AsAction;

class StockFamilyHydrateUniversalSearch
{
    use AsAction;


    public function handle(StockFamily $stockFamily): void
    {
        $stockFamily->universalSearch()->updateOrCreate(
            [],
            [
                'section'     => 'inventory',
                'title'       => join(' ', array_unique([$stockFamily->code, $stockFamily->name])),
                'description' => ''
            ]
        );
    }

}
