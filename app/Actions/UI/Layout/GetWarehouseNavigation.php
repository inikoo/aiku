<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 05 Jan 2024 01:18:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Layout;

use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetWarehouseNavigation
{
    use AsAction;

    public function handle(Warehouse $warehouse, User $user): array
    {
        $navigation = [];


        if ($user->hasPermissionTo("inventory.$warehouse->id.view")) {
            $navigation['warehouse'] = [
                'scope'   => 'warehouses',
                'label'   => __('warehouse'),
                'icon'    => ['fal', 'fa-inventory'],
                'route'   => [
                    'name'       => 'grp.org.warehouses.show',
                    'parameters' => [$warehouse->organisation->slug, $warehouse->slug]
                ],
                'topMenu' => [
                    'subSections' => [
                        [
                            'tooltip' => __('warehouses'),
                            'icon'    => ['fal', 'fa-store-alt'],
                            'route'   => [
                                'all'      => 'inventory.warehouses.index',
                                'selected' => 'inventory.warehouses.show',

                            ],
                            'label'   => [
                                'all'      => __('Warehouses'),
                                'selected' => __('Warehouse'),

                            ]
                        ],


                        [
                            'label'   => __('warehouse areas'),
                            'tooltip' => __('Warehouse Areas'),
                            'icon'    => ['fal', 'fa-map-signs'],
                            'route'   => [
                                'all'      => 'inventory.warehouse-areas.index',
                                'selected' => 'inventory.warehouses.show.warehouse-areas.index',

                            ]
                        ],
                        [
                            'label'   => __('locations'),
                            'tooltip' => __('Locations'),
                            'icon'    => ['fal', 'fa-inventory'],
                            'route'   => [
                                'all'      => 'inventory.locations.index',
                                'selected' => 'inventory.warehouses.show.locations.index',

                            ]
                        ],

                    ],


                ]
            ];
        }


        if ($user->hasPermissionTo("dispatching.$warehouse->id.view")) {
            $navigation['dispatch'] = [
                'label'   => __('Dispatch'),
                'icon'    => ['fal', 'fa-conveyor-belt-alt'],
                'route'   => 'grp.dispatch.hub',
                'topMenu' => [
                    'subSections' => []
                ]
            ];
        }


        return $navigation;
    }
}
