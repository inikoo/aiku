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

class DeleteProductCategory
{
    use AsController;
    use WithAttributes;

    public function handle(ProductCategory $department): ProductCategory
    {
        $department->products()->delete();
        $department->stats()->delete();
        $department->delete();
        return $department;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("shops.edit");
    }

    public function asController(ProductCategory $department, ActionRequest $request): ProductCategory
    {
        $request->validate();

        return $this->handle($department);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Shop $shop, ProductCategory $department, ActionRequest $request): ProductCategory
    {
        $request->validate();

        return $this->handle($department);
    }



    public function htmlResponse(ProductCategory $department): RedirectResponse
    {
        return Redirect::route('grp.shops.show', $department->shop->slug);
    }

}
