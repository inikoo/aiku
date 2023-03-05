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
use Lorisleiva\Actions\Concerns\AsAction;

class GetLayout
{
    use AsAction;

    public function handle(User $user): array
    {
        /** @var Tenant $tenant */
        $tenant    = app('currentTenant');
        $shopCount = $tenant->marketingStats->number_shops;
        $shop      = null;
        if ($shopCount == 1) {
            $shop = Shop::first();
        }

        $navigation = [];

        $navigation['dashboard'] =
            [
                'name'  => __('dashboard'),
                'icon'  => ['fal', 'fa-tachometer-alt-fast'],
                'route' => 'dashboard.show'
            ];


        /*

        if ($shopCount == 1) {
            if ($user->can('shops.view')) {
                $navigation[] = [
                    'name'            => __('shop'),
                    'icon'            => ['fal', 'fa-store-alt'],
                    'route'           => 'shops.show',
                    'routeParameters' => $shop->id
                ];
            }
            if ($user->can('websites.view')) {
                $navigation[] = [
                    'name'  => __('websites'),
                    'icon'  => ['fal', 'fa-globe'],
                    'route' => 'websites.index'
                ];
            }
            if ($user->can('shops.customers.view')) {
                $navigation[] = [
                    'name'            => __('customers'),
                    'icon'            => ['fal', 'fa-user'],
                    'route'           => 'shops.show.customers.index',
                    'routeParameters' => $shop->id

                ];
            }
        } else {
            if ($user->can('shops.view')) {
                $navigation[] = [
                    'name'  => __('shops'),
                    'icon'  => ['fal', 'fa-store-alt'],
                    'route' => 'shops.index'
                ];
            }
            if ($user->can('websites.view')) {
                $navigation[] = [
                    'name'  => __('websites'),
                    'icon'  => ['fal', 'fa-globe'],
                    'route' => 'websites.index'
                ];
            }
            if ($user->can('shops.customers.view')) {
                $navigation[] = [
                    'name'  => __('customers'),
                    'icon'  => ['fal', 'fa-user'],
                    'route' => 'customers.index'
                ];
            }
        }
*/


        if ($user->can('showroom.view')) {
            $navigation['showroom'] = [
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


        $shops = [
            'current' => new ShopsNavigationResource(Shop::latest()->first()),
            'items'   => ShopsNavigationResource::collection(Shop::all())
        ];

        return [
            'navigation' => $navigation,
            'actions'    => $actions,
            'shops'      => $shops
        ];
    }
}
