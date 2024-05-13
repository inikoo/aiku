<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 13:12:05 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ProductCategory;

use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DeleteFamily
{
    use AsController;
    use WithAttributes;

    public function handle(ProductCategory $family): ProductCategory
    {
        $family->products()->delete();
        $family->stats()->delete();
        $family->delete();
        return $family;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("shops.edit");
    }

    public function asController(ProductCategory $family, ActionRequest $request): ProductCategory
    {
        $request->validate();

        return $this->handle($family);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Shop $shop, ProductCategory $family, ActionRequest $request): ProductCategory
    {
        $request->validate();

        return $this->handle($family);
    }



    public function htmlResponse(ProductCategory $family): RedirectResponse
    {
        return Redirect::route('grp.shops.show', $family->shop->slug);
    }

}
