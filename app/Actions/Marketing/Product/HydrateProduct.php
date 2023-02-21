<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 21 Feb 2023 22:01:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Product;


use App\Actions\HydrateModel;
use App\Models\Marketing\Product;
use Illuminate\Support\Collection;


class HydrateProduct extends HydrateModel
{

    public string $commandSignature = 'hydrate:product {tenants?*} {--i|id=} ';


    public function handle(Product $product): void
    {

    }


    protected function getModel(int $id): Product
    {
        return Product::find($id);
    }

    protected function getAllModels(): Collection
    {
        return Product::withTrashed()->all();
    }

}


