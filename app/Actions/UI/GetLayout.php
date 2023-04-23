<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 07 Sept 2022 22:03:00 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\UI;

use App\Http\Resources\UI\ShopsNavigationResource;
use App\Models\Marketing\Shop;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetLayout
{
    use AsAction;

    public function handle(User $user): array
    {
        $tenant    = app('currentTenant');
        $shopCount = $tenant->marketingStats->number_shops;


        $currentShopInstance = null;

        if ($shopCount == 1) {
            $currentShopInstance = Shop::first();
        }


        $navigation = [];

        $navigation['dashboard'] =
            [
                'name'  => __('dashboard'),
                'icon'  => ['fal', 'fa-tachometer-alt-fast'],
                'route' => 'dashboard.show'
            ];


        if ($user->can('shops.products.view')) {
            $navigation['shops'] = match ($shopCount) {
                1 => [
                    'name'            => __('shop'),
                    'icon'            => ['fal', 'fa-store-alt'],
                    'route'           => 'shops.show',
                    'routeParameters' => [$currentShopInstance->slug]
                ],
                default => [
                    'name'  => __('shops'),
                    'icon'  => ['fal', 'fa-store-alt'],
                    'route' => 'shops.index'
                ]
            };
        }


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


        return [
            'navigation'      => $navigation,
            'actions'         => $actions,
            'shopsInDropDown' => ShopsNavigationResource::collection(Shop::all()),

        ];
    }
}
