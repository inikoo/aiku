<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 10:58:06 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\WarehouseArea\UI;

use App\Actions\InertiaAction;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class RemoveWarehouseArea extends InertiaAction
{
    public function handle(WarehouseArea $warehouseArea): WarehouseArea
    {
        return $warehouseArea;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("inventory.edit");
    }

    public function asController(WarehouseArea $warehouseArea, ActionRequest $request): WarehouseArea
    {
        $this->initialisation($request);

        return $this->handle($warehouseArea);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouse(Warehouse $warehouse, WarehouseArea $warehouseArea, ActionRequest $request): WarehouseArea
    {
        $this->initialisation($request);

        return $this->handle($warehouseArea);
    }


    public function getAction($route): array
    {
        return  [
            'buttonLabel' => __('Delete'),
            'title'       => __('Delete Warehouse Area'),
            'text'        => __("This action will delete this Warehouse Area and all it's Locations"),
            'route'       => $route
        ];
    }

    public function htmlResponse(WarehouseArea $warehouseArea, ActionRequest $request): Response
    {

        return Inertia::render(
            'RemoveModel',
            [
                'title'       => __('delete warehouse area'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'pageHead'    => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'fa-map-signs'],
                            'title' => __('warehouse area')
                        ],
                    'title'  => $warehouseArea->slug,
                    'actions'=> [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'label' => __('cancel'),
                            'route' => [
                                'name'       => preg_replace('/remove$/', 'show', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ]
                    ]
                ],
                'data'     => $this->getAction(
                    route:
                    match ($request->route()->getName()) {
                        'grp.oms.warehouse-areas.remove' => [
                            'name'       => 'grp.models.warehouse-area.delete',
                            'parameters' => $request->route()->originalParameters()
                        ],
                        'grp.oms.warehouses.show.warehouse-areas.remove' => [
                            'name'       => 'grp.models.warehouse.warehouse-area.delete',
                            'parameters' => $request->route()->originalParameters()
                        ]
                    }
                )




            ]
        );
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowWarehouseArea::make()->getBreadcrumbs(
            $routeName,
            routeParameters: $routeParameters,
            suffix: '('.__('deleting').')'
        );
    }
}
