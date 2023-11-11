<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Market\Product\Hydrators;

use App\Actions\Traits\WithOrganisationJob;
use App\Models\Market\Product;
use Lorisleiva\Actions\Concerns\AsAction;

class ProductHydrateUniversalSearch
{
    use AsAction;
    use WithOrganisationJob;

    public function handle(Product $product): void
    {
        $product->universalSearch()->updateOrCreate(
            [],
            [
                'section'     => 'shops',
                'title'       => $product->name,
                'description' => $product->code
            ]
        );
    }

}
