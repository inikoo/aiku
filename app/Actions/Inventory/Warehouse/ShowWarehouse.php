<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 15 Sept 2022 14:41:26 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Warehouse;

use App\Actions\Inventory\ShowInventoryDashboard;
use App\Actions\UI\WithInertia;
use App\Http\Resources\Inventory\WarehouseResource;
use App\Models\Inventory\Warehouse;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;


/**
 * @property Warehouse $warehouse
 */
class ShowWarehouse
{
    use AsAction;
    use WithInertia;


    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("inventory.view");
    }

    public function asController(Warehouse $warehouse): void
    {
        $this->warehouse    = $warehouse;
    }

    public function htmlResponse(): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Inventory/Warehouse',
            [
                'title'       => __('warehouse'),
                'breadcrumbs' => $this->getBreadcrumbs($this->warehouse),
                'pageHead'    => [
                    'icon'  => 'fal fa-warehouse',
                    'title' => $this->warehouse->name,
                    'meta'  => [
                        [
                            'name'     => trans_choice('warehouse area|warehouse areas', $this->warehouse->stats->number_warehouse_areas),
                            'number'   => $this->warehouse->stats->number_warehouse_areas,
                            'href'     => [
                                'inventory.warehouses.show.warehouse_areas.index',
                                $this->warehouse->id
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-map-signs',
                                'tooltip' => __('warehouse areas')
                            ]
                        ],
                        [
                            'name'     => trans_choice('location|locations', $this->warehouse->stats->number_locations),
                            'number'   => $this->warehouse->stats->number_locations,
                            'href'     => [
                                'inventory.warehouses.show.locations.index',
                                $this->warehouse->id
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-inventory',
                                'tooltip' => __('locations')
                            ]
                        ]
                    ]

                ],
                'warehouse'   => $this->warehouse
            ]
        );
    }


    #[Pure] public function jsonResponse(): WarehouseResource
    {
        return new WarehouseResource($this->warehouse);
    }


    public function getBreadcrumbs(Warehouse $warehouse): array
    {
        return array_merge(
            (new ShowInventoryDashboard())->getBreadcrumbs(),
            [
                'inventory.warehouses.show' => [
                    'route'           => 'inventory.warehouses.show',
                    'routeParameters' => $warehouse->slug,
                    'name'            => $warehouse->code,
                    'index'           => [
                        'route'   => 'inventory.warehouses.index',
                        'overlay' => __('warehouses list')
                    ],
                    'modelLabel'      => [
                        'label' => __('warehouse')
                    ],
                ],
            ]
        );
    }

}
