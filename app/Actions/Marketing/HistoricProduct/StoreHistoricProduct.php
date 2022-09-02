<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 29 Sep 2021 16:47:56 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Marketing\HistoricProduct;

use App\Actions\StoreModelAction;
use App\Models\Utils\ActionResult;
use App\Models\Marketing\Product;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreHistoricProduct extends StoreModelAction
{
    use AsAction;

    public function handle(Product $product, array $modelData): ActionResult
    {

        $modelData['organisation_id']=$product->organisation_id;
        $historicProduct = $product->historicRecords()->create($modelData);

       return $this->finalise($historicProduct);
    }
}
