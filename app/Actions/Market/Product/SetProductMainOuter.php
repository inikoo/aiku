<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Apr 2024 10:03:05 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Product;

use App\Models\Market\Outer;
use App\Models\Market\Product;
use Lorisleiva\Actions\Concerns\AsAction;

class SetProductMainOuter
{
    use AsAction;

    public function handle(Product $product, Outer $mainOuter): Product
    {
        $product->update(
            [
                'main_outer_id'       => $mainOuter->id,
                'main_outer_units'    => $mainOuter->units,
                'main_outer_price'    => $mainOuter->price,
                'main_outer_available'=> $mainOuter->available,
            ]
        );

        $product->outers()->where('id', '!=', $mainOuter->id)->update(['is_main' => false]);
        $mainOuter->update(['is_main' => true]);




        return $product;
    }

}
