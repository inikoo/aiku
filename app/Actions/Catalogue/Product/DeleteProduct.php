<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 22 Feb 2023 12:31:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Product;

use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateProducts;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use Illuminate\Console\Command;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class DeleteProduct
{
    use WithActionUpdate;

    public string $commandSignature = 'delete:product {tenant} {id}';

    public function handle(Product $product, array $deletedData = [], bool $skipHydrate = false): Product
    {
        $product->delete();
        $product->outers()->delete();
        $product = $this->update($product, $deletedData, ['data']);
        if (!$skipHydrate) {
            //todo fix this
            /*
            if ($product->family_id) {
                FamilyHydrateProducts::dispatch($product->family);
            }
            */
            ShopHydrateProducts::dispatch($product->shop);
        }

        return $product;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("shops.edit");
    }

    public function asController(Product $product, ActionRequest $request): Product
    {
        $request->validate();

        return $this->handle($product);
    }

    public function inShop(Shop $shop, Product $product, ActionRequest $request): Product
    {
        $request->validate();

        return $this->handle($product);
    }

    public function asCommand(Command $command): int
    {
        Organisation::where('slug', $command->argument('tenant'))->first()->makeCurrent();
        $this->handle(Product::findOrFail($command->argument('id')));
        return 0;
    }

    public function htmlResponse(Product $product): RedirectResponse
    {
        return Redirect::route('grp.shops.show', $product->shop->slug);
    }
}
