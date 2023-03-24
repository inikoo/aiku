<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 22 Feb 2023 12:31:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Product;

use App\Actions\Marketing\Family\Hydrators\FamilyHydrateProducts;
use App\Actions\Marketing\Shop\Hydrators\ShopHydrateProducts;
use App\Actions\WithActionUpdate;
use App\Models\Central\Tenant;
use App\Models\Marketing\Product;
use Illuminate\Console\Command;

class DeleteProduct
{
    use WithActionUpdate;

    public string $commandSignature = 'delete:product {tenant} {id}';

    public function handle(Product $product, array $deletedData = [], bool $skipHydrate = false): Product
    {
        $product->delete();
        $product->historicRecords()->delete();
        $product = $this->update($product, $deletedData, ['data']);
        if (!$skipHydrate) {
            if ($product->family_id) {
                FamilyHydrateProducts::dispatch($product->family);
            }
            ShopHydrateProducts::dispatch($product->shop);
        }

        return $product;
    }


    public function asCommand(Command $command): int
    {
        Tenant::where('slug', $command->argument('tenant'))->first()->makeCurrent();
        $this->handle(Product::findOrFail($command->argument('id')));
        return 0;
    }
}
