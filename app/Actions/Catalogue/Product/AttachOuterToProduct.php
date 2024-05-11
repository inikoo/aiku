<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 Apr 2024 12:58:15 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Product;

use App\Actions\Catalogue\Product\Hydrators\ProductHydrateOuters;
use Lorisleiva\Actions\Concerns\AsAction;

class AttachOuterToProduct
{
    use AsAction;

    public function handle($product, $outer)
    {
        $product->outers()->attach($outer);
        ProductHydrateOuters::run($product);
        return $product;
    }
}
