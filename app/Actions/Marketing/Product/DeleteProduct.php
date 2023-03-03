<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 22 Feb 2023 12:31:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Product;

use App\Actions\Marketing\Family\HydrateFamily;
use App\Actions\Marketing\Shop\HydrateShop;
use App\Actions\WithActionUpdate;
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
                HydrateFamily::make()->productsStats($product->family);
            }
            HydrateShop::make()->productsStats($product->shop);
        }

        return $product;
    }


    public function asCommand(Command $command): int
    {
        $tenant = tenancy()->query()->where('code', $command->argument('tenant'))->first();
        tenancy()->initialize($tenant);

        $this->handle(Product::findOrFail($command->argument('id')));

        return 0;
    }
}
