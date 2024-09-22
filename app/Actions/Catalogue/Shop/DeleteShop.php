<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 13:12:05 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shop;

use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateCustomers;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateDepartments;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateFamilies;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateProducts;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateShops;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateDepartments;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateFamilies;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateProducts;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateShops;
use App\Actions\Web\Website\DeleteWebsite;
use App\Models\Catalogue\Shop;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class DeleteShop extends OrgAction
{
    public function handle(Shop $shop): Shop
    {

        DeleteWebsite::make()->action($shop->website);

        $shop->prospects()->delete();
        $shop->customers()->delete();
        $shop->products()->delete();
        $shop->productCategories()->delete();
        $shop->delete();
        GroupHydrateShops::dispatch($shop->group);
        GroupHydrateCustomers::dispatch($shop->group);
        GroupHydrateProducts::dispatch($shop->group);
        GroupHydrateFamilies::dispatch($shop->group);
        GroupHydrateDepartments::dispatch($shop->group);

        OrganisationHydrateShops::dispatch($shop->organisation);
        OrganisationHydrateShops::dispatch($shop->organisation);
        OrganisationHydrateProducts::dispatch($shop->organisation);
        OrganisationHydrateFamilies::dispatch($shop->organisation);
        OrganisationHydrateDepartments::dispatch($shop->organisation);

        return $shop;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("supervisor-products.{$this->shop->id}");
    }

    public function afterValidator(Validator $validator, ActionRequest $request): void
    {
        if($this->shop->orders()->exists()) {
            $validator->errors()->add('orders', 'This shop has orders associated with it.');
        }


    }

    public function asController(Shop $shop, ActionRequest $request): Shop
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop);
    }



}
