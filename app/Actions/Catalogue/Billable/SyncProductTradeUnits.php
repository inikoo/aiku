<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Apr 2023 21:13:56 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Billable;

use App\Models\Catalogue\Billable;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncProductTradeUnits
{
    use AsAction;

    public function handle(Billable $product, array $tradeUnitsData): Billable
    {
        $product->tradeUnits()->sync($tradeUnitsData);

        //SyncProductTradeUnitImages::run($product);


        return $product;
    }
}
