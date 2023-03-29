<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 07 Sept 2022 22:03:00 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\UI;

use App\Http\Resources\UI\ShopsNavigationResource;
use App\Models\Central\Tenant;
use App\Models\Marketing\Shop;
use App\Models\SysAdmin\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class GetLayout
{
    use AsAction;

    public function handle(User $user): array
    {
        /** @var Tenant $tenant */
        $tenant = app('currentTenant');


        $navigation = [];

        $navigation['dashboard'] =
            [
                'name'  => __('dashboard'),
                'icon'  => ['fal', 'fa-tachometer-alt-fast'],
                'route' => 'dashboard.show'
            ];

        /*
         * TODO add shop dependent links here
        if ($user->can('shops.products.view')) {
            $navigation['catalogue'] = [
                'name'  => 'Showroom',
                'icon'  => ['fal', 'fa-store-alt'],
                'route' => 'showroom.dashboard'
            ];
        }


        if ($user->can('crm.view')) {
            $navigation['crm'] = [
                'name'  => 'CRM',
                'icon'  => ['fal', 'fa-user'],
                'route' => 'crm.dashboard'
            ];
        }


        if ($user->can('osm.view')) {
            $navigation['osm'] = [
                'name'  => 'OSM',
                'icon'  => ['fal', 'fa-shopping-cart'],
                'route' => 'osm.hub'
            ];
        }

        */

        if ($user->can('dispatch')) {
            $navigation['dispatch'] = [
                'name'  => __('Dispatch'),
                'icon'  => ['fal', 'fa-conveyor-belt-alt'],
                'route' => 'dispatch.hub'
            ];
        }

        if ($user->can('inventory.view')) {
            $navigation['inventory'] = [
                'name'  => __('inventory'),
                'icon'  => ['fal', 'fa-inventory'],
                'route' => 'inventory.dashboard'
            ];
        }

        if ($user->can('fulfilment.view')) {
            $navigation['fulfilment'] = [
                'name'  => __('fulfilment'),
                'icon'  => ['fal', 'fa-dolly-empty'],
                'route' => 'fulfilment.dashboard'
            ];
        }

        if ($user->can('production.view')) {
            $navigation['production'] = [
                'name'  => __('production'),
                'icon'  => ['fal', 'fa-industry'],
                'route' => 'production.dashboard'
            ];
        }

        if ($user->can('procurement.view')) {
            $navigation['procurement'] = [
                'name'  => __('procurement'),
                'icon'  => ['fal', 'fa-parachute-box'],
                'route' => 'procurement.dashboard'
            ];
        }
        if ($user->can('accounting.view')) {
            $navigation['accounting'] = [
                'name'  => __('Accounting'),
                'icon'  => ['fal', 'fa-abacus'],
                'route' => 'accounting.dashboard'
            ];
        }


        if ($user->can('hr.view')) {
            $navigation['hr'] = [
                'name'  => __('human resources'),
                'icon'  => ['fal', 'fa-user-hard-hat'],
                'route' => 'hr.dashboard'
            ];
        }

        if ($user->can('sysadmin.view')) {
            $navigation['sysadmin'] = [
                'name'  => __('Sysadmin'),
                'icon'  => ['fal', 'fa-users-cog'],
                'route' => 'sysadmin.dashboard'
            ];
        }


        $actions = [];

        if ($user->can('dispatching.pick')) {
            $actions[] = [
                'name'  => __('picking'),
                'icon'  => ['fal', 'fa-dolly-flatbed-alt'],
                'route' => 'dashboard.show',
                'color' => 'bg-indigo-500'
            ];
        }

        if ($user->can('dispatching.pack')) {
            $actions[] = [
                'name'  => __('packing'),
                'icon'  => ['fal', 'fa-conveyor-belt-alt'],
                'route' => 'dashboard.show',
                'color' => 'bg-green-500'
            ];
        }


        $shopCount       = $tenant->marketingStats->number_shops;
        $currentShop     = null;
        $currentShopSlug = null;

        if ($shopCount == 1) {
            $currentShopInstance = Shop::first();
            $currentShop         = new ShopsNavigationResource($currentShopInstance);
            $currentShopSlug     = $currentShopInstance->slug;
        } elseif ($shopCount > 1) {
            $routeName       = Route::current()->getName();

            if (Str::startsWith($routeName, 'shops.show')) {
                $currentShopInstance = Route::current()->parameters()['shop'];
                $currentShop         = new ShopsNavigationResource($currentShopInstance);
                $currentShopSlug     = $currentShopInstance->slug;
            } elseif (!Str::startsWith($routeName, ['customers', 'orders', 'products', 'websites'])) {
                if (session()->has('currentShop')) {
                    $currentShopInstance = Shop::where('slug', session('currentShop'))->first();
                    $currentShop         = new ShopsNavigationResource($currentShopInstance);
                    $currentShopSlug     = $currentShopInstance->slug;
                }
            }
        }


        session(['currentShop' => $currentShopSlug]);

        $shops = [
            'count'   => $shopCount,
            'current' => $currentShop,
            'items'   => ShopsNavigationResource::collection(Shop::all())
        ];

        return [
            'navigation' => $navigation,
            'actions'    => $actions,
            'shops'      => $shops
        ];
    }
}
