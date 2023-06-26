<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 10:58:06 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\WarehouseArea\UI;

use App\Actions\InertiaAction;
use App\Actions\Procurement\Agent\UI\ShowAgent;
use App\Models\Inventory\Location;
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
            'title'       => __('Delete warehouse area'),
            'text'        => __("This action will delete this warehouse area and all it's locations"),
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
                    $request->route()->parameters
                ),
                'pageHead'    => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'fa-map-signs'],
                            'title' => __('warehouse area')
                        ],
                    'title' => $warehouseArea->slug,

                    'exitEdit' => [
                        'label' => __('Cancel'),
                        'route' => [
                            'name'       => preg_replace('/remove$/', 'show', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ],
                ],
                'data'     => $this->getAction(
                    route:[
                        'name'       => 'models.warehouse-area.delete',
                        'parameters' => array_values($this->originalParameters)
                    ]
                )




            ]
        );
    }


    public function getBreadcrumbs(array $routeParameters): array
    {
        return ShowAgent::make()->getBreadcrumbs(
            routeParameters: $routeParameters,
            suffix: '('.__('deleting').')'
        );
    }
}
