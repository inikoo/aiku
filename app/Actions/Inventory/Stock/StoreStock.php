<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 29 Oct 2021 12:56:07 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Inventory\Stock;

use App\Models\Central\Tenant;
use App\Models\CRM\Customer;
use App\Models\Inventory\Stock;
use Lorisleiva\Actions\Concerns\AsAction;


class StoreStock
{
    use AsAction;

    public function handle(Tenant|Customer $owner,$modelData): Stock
    {

        $modelData['owner_id']=$owner->id;
        $modelData['owner_type']=$owner::class;

        /** @var Stock $stock */
        $stock = Stock::create($modelData);
        $stock->stats()->create();

        return $stock;
    }
}
