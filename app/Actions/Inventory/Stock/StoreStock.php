<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 29 Oct 2021 12:56:07 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Inventory\Stock;

use App\Models\Central\Tenant;
use App\Models\Inventory\Stock;
use App\Models\Sales\Customer;
use Lorisleiva\Actions\Concerns\AsAction;


class StoreStock
{
    use AsAction;

    public function handle(Tenant|Customer $owner,$modelData): Stock
    {

        /** @var Stock $stock */
        $stock = $owner->stocks()->create($modelData);
        $stock->stats()->create();

        return $stock;
    }
}
