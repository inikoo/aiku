<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 15:09:39 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Product;

use App\Actions\Marketing\Product\Hydrators\ProductHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Models\Marketing\Product;

class UpdateProduct
{
    use WithActionUpdate;

    public function handle(Product $product, array $modelData, bool $skipHistoric=false): Product
    {
        $product= $this->update($product, $modelData, ['data', 'settings']);
        if (!$skipHistoric and $product->wasChanged(
            ['price', 'code','name','units']
        )) {
            //todo create HistoricProduct and update current_historic_product_id if
        }
        ProductHydrateUniversalSearch::dispatch($product);

        return $product;
    }
}
