<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 07 Sept 2022 22:03:00 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\UI;

use App\Http\Resources\UI\ShopsNavigationResource;
use App\Http\Resources\UI\WarehousesNavigationResource;
use App\Models\Auth\User;
use App\Models\Inventory\Warehouse;
use App\Models\Marketing\Shop;
use Lorisleiva\Actions\Concerns\AsAction;

class GetLayout
{
    use AsAction;

    public function handle(User $user): array
    {
        $tenant              = app('currentTenant');
        $shopCount           = $tenant->marketingStats->number_shops;
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


        if ($user->can('shops.customers.view')) {
            $navigation['customers'] = [
                'name'  => __('CRM'),
                'icon'  => ['fal', 'fa-tasks-alt'],
                'route' => 'customers.index'
            ];
        }

        if ($user->can('shops.customers.view')) {
            $navigation['marketing'] = [
                'name'  => __('Marketing'),
                'icon'  => ['fal', 'fa-bullhorn'],
                'route' => 'customers.index'
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
                'name'    => __('inventory'),
                'icon'    => ['fal', 'fa-inventory'],
                'route'   => 'inventory.dashboard',
                'topMenu' => [
                    [
                        'label' => __('stocks'),
                        'icon'  => ['fal', 'fa-box'],
                        'route' => [
                            'name' => 'inventory.stocks.index',

                        ]
                    ],
                    [
                        'label'  => __('categories'),
                        'tooltip'=> __('Stock categories'),
                        'icon'   => ['fal', 'fa-boxes-alt'],
                        'route'  => [
                            'name' => 'inventory.stock-families.index',

                        ]
                    ],

                ]
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
                'icon'  => ['fal', 'fa-box-usd'],
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
                'name'    => __('human resources'),
                'icon'    => ['fal', 'fa-user-hard-hat'],
                'route'   => 'hr.dashboard',
                'topMenu' => [
                    [
                        'label' => __('work positions'),
                        'icon'  => ['fal', 'fa-network-wired'],
                        'route' => [
                            'name' => 'hr.job-positions.index',

                        ]
                    ],
                    [
                        'label' => __('employees'),
                        'icon'  => ['fal', 'fa-terminal'],
                        'route' => [
                            'name' => 'hr.employees.index',

                        ]
                    ],
                    [
                        'label' => __('calendar'),
                        'icon'  => ['fal', 'fa-calendar'],
                        'route' => [
                            'name' => 'hr.calendar',

                        ]
                    ],
                    [
                        'label' => __('time sheets'),
                        'icon'  => ['fal', 'fa-stopwatch'],
                        'route' => [
                            'name' => 'hr.time-sheets.hub',

                        ]
                    ],
                    [
                        'label' => __('clocking machines'),
                        'icon'  => ['fal', 'fa-chess-clock'],
                        'route' => [
                            'name' => 'hr.clocking-machines.index',

                        ]
                    ]
                ]
            ];
        }

        if ($user->can('sysadmin.view')) {
            $navigation['sysadmin'] = [
                'name'    => __('sysadmin'),
                'icon'    => ['fal', 'fa-users-cog'],
                'route'   => 'sysadmin.dashboard',
                'topMenu' => [
                    [
                        'label' => __('users'),
                        'icon'  => ['fal', 'fa-terminal'],
                        'route' => [
                            'name' => 'sysadmin.users.index',

                        ]
                    ],
                    [
                        'label' => __('guests'),
                        'icon'  => ['fal', 'fa-user-alien'],
                        'route' => [
                            'name' => 'sysadmin.guests.index',

                        ]
                    ],
                    [
                        'label' => __('system settings'),
                        'icon'  => ['fal', 'fa-cog'],
                        'route' => [
                            'name' => 'sysadmin.settings',

                        ]
                    ],

                ]
            ];
        }


        $secondaryNavigation = [];


        if ($user->can('fulfilment.view')) {
            $secondaryNavigation['fulfilment'] = [
                'name'  => __('fulfilment'),
                'icon'  => ['fal', 'fa-dolly-empty'],
                'route' => 'fulfilment.dashboard'
            ];
        }

        if ($user->can('shops.view')) {
            $secondaryNavigation['dropshipping'] = [
                'name'  => __('dropshipping'),
                'icon'  => ['fal', 'fa-parachute-box'],
                'route' => 'dropshipping.dashboard'
            ];
        }


        return [
            'navigation'           => $navigation,
            'secondaryNavigation'  => $secondaryNavigation,
            'shopsInDropDown'      => ShopsNavigationResource::collection(Shop::all()),
            'warehousesInDropDown' => WarehousesNavigationResource::collection(Warehouse::all()),

        ];
    }
}
