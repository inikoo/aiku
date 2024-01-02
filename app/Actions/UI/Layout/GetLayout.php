<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 08 Dec 2023 22:08:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Layout;

use App\Http\Resources\SysAdmin\Group\GroupResource;
use App\Http\Resources\SysAdmin\Organisation\UserOrganisationResource;
use App\Http\Resources\UI\ShopsNavigationResource;
use App\Http\Resources\UI\WarehousesNavigationResource;
use App\Http\Resources\UI\WebsitesNavigationResource;
use App\Models\Inventory\Warehouse;
use App\Models\Market\Shop;
use App\Models\SysAdmin\User;
use App\Models\Web\Website;
use Lorisleiva\Actions\Concerns\AsAction;

class GetLayout
{
    use AsAction;

    public function handle(?User $user): array
    {
        if (!$user) {
            return [];
        }

        return [

            'group'         => GroupResource::make(app('group'))->getArray(),
            'organisations' => UserOrganisationResource::collectionForUser($user->authorisedOrganisations, $user),

            'navigation' => [
                'grp' => GetGroupNavigation::run($user),
                'org' => GetOrganisationsLayout::run($user),
            ]


            // 'shopsInDropDown'      => ShopsNavigationResource::collection(Shop::all()),
            // 'websitesInDropDown'   => WebsitesNavigationResource::collection(Website::all()),
            // 'warehousesInDropDown' => WarehousesNavigationResource::collection(Warehouse::all()),
            /*

                      'layoutCurrentShopSlug' => function () use ($user) {
                          if ($user) {
                              return GetCurrentShopSlug::run($user);
                          } else {
                              return null;
                          }
                      },


                      'layoutShopsList'      => function () use ($user) {
                          if ($user) {
                              return GetShops::run($user);
                          } else {
                              return [];
                          }
                      },
                      'layoutWebsitesList'   => function () use ($user) {
                          if ($user) {
                              return GetWebsites::run($user);
                          } else {
                              return [];
                          }
                      },
                      'layoutWarehousesList' => function () use ($user) {
                          if ($user) {
                              return GetWarehouses::run($user);
                          } else {
                              return [];
                          }
                      },

                      'layout' => function () use ($user) {
                          if ($user) {
                              return GetLayout::run($user);
                          } else {
                              return [];
                          }
                      }

                      */


        ];
    }
}
