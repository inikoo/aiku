<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 14 Sept 2022 15:12:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory;

use App\Actions\UI\WithInertia;
use App\Models\Organisations\Organisation;
use App\Models\SysAdmin\User;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;


/**
 * @property Organisation|null $organisation
 * @property User $user
 */
class ShowInventoryDashboard
{
    use AsAction;
    use WithInertia;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("inventory.view");
    }


    public function asController(ActionRequest $request): void
    {
        $this->user         = $request->user();
        $this->organisation = $this->user->currentUiOrganisation;
    }


    public function htmlResponse(): Response
    {
        $this->validateAttributes();


        if ($this->organisation->inventoryStats->number_warehouses == 1) {

            $warehouseID=$this->organisation->firstWarehouse->id;

            $warehousesNode    = [
                'name' => __('warehouse'),
                'icon' => ['fal', 'fa-warehouse'],
                'href' => ['inventory.warehouses.show', $warehouseID],

            ];
            $warehouseAreasNode = [
                'name'  => __('warehouses areas'),
                'icon'  => ['fal', 'fa-map-signs'],
                'href'  => ['inventory.warehouses.show.warehouse_areas.index',$warehouseID],
                'index' => [
                    'number' => $this->organisation->inventoryStats->number_warehouse_areas
                ]
            ];
            $locationsNode    = [
                'name'  => __('locations'),
                'icon'  => ['fal', 'fa-inventory'],
                'href'  => ['inventory.warehouses.show.locations.index',$warehouseID],
                'index' => [
                    'number' => $this->organisation->inventoryStats->number_locations
                ]

            ];
        } else {
            $warehousesNode = [
                'name'  => __('warehouses'),
                'icon'  => ['fal', 'fa-warehouse'],
                'href'  => ['inventory.warehouses.index'],
                'index' => [
                    'number' => $this->organisation->inventoryStats->number_warehouses
                ]
            ];
            $warehouseAreasNode = [
                'name'  => __('warehouses areas'),
                'icon'  => ['fal', 'fa-map-signs'],
                'href'  => ['inventory.warehouse_areas.index'],
                'index' => [
                    'number' => $this->organisation->inventoryStats->number_warehouse_areas
                ]
            ];
            $locationsNode    = [
                'name'  => __('locations'),
                'icon'  => ['fal', 'fa-inventory'],
                'href'  => ['inventory.locations.index'],
                'index' => [
                    'number' => $this->organisation->inventoryStats->number_locations
                ]

            ];
        }


        return Inertia::render(
            'Inventory/InventoryDashboard',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('inventory'),
                'pageHead'    => [
                    'title' => __('inventory'),
                ],
                'treeMaps'    => [
                    [
                        $warehousesNode,
                        $warehouseAreasNode,
                        $locationsNode
                    ],
                    [
                        [
                            'name' => __('Stocks'),
                            'icon' => ['fal', 'fa-warehouse'],
                            'href' => ['inventory.warehouses.index']
                        ]
                    ]
                ]

            ]

        );
    }


    public function getBreadcrumbs(): array
    {
        return [
            'inventory.dashboard' => [
                'route' => 'inventory.dashboard',
                'name'  => __('inventory'),
            ]
        ];
    }


}
