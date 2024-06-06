<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 13:12:05 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shop;

use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateShops;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateShops;
use App\Models\Catalogue\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DeleteShop
{
    use AsController;
    use WithAttributes;

    public function handle(Shop $shop): Shop
    {
        $shop->website()->delete();
        $shop->prospects()->delete();
        $shop->products()->delete();
        $shop->productCategories()->delete();
        $shop->delete();
        GroupHydrateShops::dispatch($shop->group);
        OrganisationHydrateShops::dispatch($shop->organisation);
        return $shop;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("shops.edit");
    }

    public function asController(Shop $shop, ActionRequest $request): Shop
    {
        $request->validate();

        return $this->handle($shop);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::route('grp.org.shops.index');
    }

}
