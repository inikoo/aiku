<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 29 Sep 2021 16:47:56 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Marketing\Product;

use App\Models\Central\Tenant;
use App\Models\Marketing\Product;
use App\Models\Marketing\Shop;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreProduct
{
    use AsAction;

    public function handle(Shop $shop, array $modelData): Product
    {
        /** @var Product $product */
        $product = $shop->products()->create($modelData);

        $product->salesStats()->create([
                                           'scope' => 'sales'
                                       ]);
       /** @var Tenant $tenant */
        $tenant=tenant();
        if ($product->shop->currency_id != $tenant->currency_id) {
            $product->salesStats()->create([
                                               'scope' => 'sales-tenant-currency'
                                           ]);
        }

        return $product;
    }
}
