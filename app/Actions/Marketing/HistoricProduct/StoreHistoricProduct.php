<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 29 Sep 2021 16:47:56 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Marketing\HistoricProduct;

use App\Models\Marketing\HistoricProduct;
use App\Models\Marketing\Product;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreHistoricProduct
{
    use AsAction;

    public function handle(Product $product, array $modelData): HistoricProduct
    {
        /** @var HistoricProduct $historicProduct */
         $historicProduct= $product->historicRecords()->create($modelData);
         return $historicProduct;

    }
}
