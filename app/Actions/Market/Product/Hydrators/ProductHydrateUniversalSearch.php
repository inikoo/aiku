<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Market\Product\Hydrators;

use App\Actions\Traits\WithTenantJob;
use App\Models\Market\Product;
use Lorisleiva\Actions\Concerns\AsAction;

class ProductHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;

    public function handle(Product $product): void
    {
        $product->universalSearch()->create(
            [
                'primary_term'   => $product->name,
                'secondary_term' => $product->code
            ]
        );
    }

}
