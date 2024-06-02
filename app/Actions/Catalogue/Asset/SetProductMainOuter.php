<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Apr 2024 10:03:05 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Asset;

use App\Models\Catalogue\Product;
use App\Models\Catalogue\Asset;
use Lorisleiva\Actions\Concerns\AsAction;

class SetProductMainOuter
{
    use AsAction;

    public function handle(Asset $product, Product $mainOuter): Asset
    {


        $product->update(
            [
                'code'                                        => $mainOuter->code,
                'name'                                        => $mainOuter->name,
                'main_outerable_id'                           => $mainOuter->id,
                'price'                                       => $mainOuter->price,
                'main_outerable_available_quantity'           => $mainOuter->available_quantity,
            ]
        );

        $product->outers()->where('id', '!=', $mainOuter->id)->update(['is_main' => false]);
        $mainOuter->update(['is_main' => true]);




        return $product;
    }

}
