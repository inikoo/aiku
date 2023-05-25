<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Inventory\InventoryDashboard;
use App\Enums\UI\WarehouseTabsEnum;
use App\Http\Resources\Inventory\WarehouseResource;
use App\Models\Inventory\Warehouse;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Warehouse $warehouse
 */
class ShowStoredItem extends InertiaAction
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('fulfilment.edit');

        return $request->user()->hasPermissionTo("fulfilment.view");
    }

    public function asController(Warehouse $warehouse, ActionRequest $request): void
    {
        $this->initialisation($request)->withTab(WarehouseTabsEnum::values());
        $this->warehouse = $warehouse;
    }


    public function htmlResponse(): Response
    {
        return Inertia::render(
            'Inventory/Warehouse',
            [
                'title'       => __('warehouse'),
                'breadcrumbs' => $this->getBreadcrumbs($this->warehouse),
                'pageHead'    => [
                    'icon'          =>
                        [
                            'icon'  => ['fal', 'warehouse'],
                            'title' => __('warehouse')
                        ],
                    'title' => $this->warehouse->name,
                    /*
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,
                    */



                ],
                'tabs'        => [

                    'current'    => $this->tab,
                    'navigation' => WarehouseTabsEnum::navigation(),


                ],




            ]
        );
    }


    public function jsonResponse(): WarehouseResource
    {
        return new WarehouseResource($this->warehouse);
    }

    public function getBreadcrumbs(Warehouse $warehouse, $suffix = null): array
    {
        return array_merge(
            (new InventoryDashboard())->getBreadcrumbs(),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name' => 'inventory.warehouses.index',
                            ],
                            'label' => __('warehouse')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'inventory.warehouses.show',
                                'parameters' => [$warehouse->slug]
                            ],
                            'label' => $warehouse->code,
                        ],
                    ],
                    'suffix'         => $suffix,

                ],
            ]
        );
    }
}
