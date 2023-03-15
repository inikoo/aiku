<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:34:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\WarehouseArea\UI;

use App\Actions\InertiaAction;
use App\Models\Inventory\Warehouse;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateWarehouseArea extends InertiaAction
{
    use HasUIWarehouseAreas;
    public function handle(): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->parent),
                'title'       => __('new warehouse area'),
                'pageHead'    => [
                    'title'        => __('new warehouse area'),
                    'cancelCreate' => [
                        'route' => [
                            'name'       => 'inventory.warehouse_areas.index',
                            'parameters' => array_values($this->originalParameters)
                        ],
                    ]

                ],


            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('inventory.warehouse_areas.edit');
    }


    public function asController(Warehouse $warehouse, ActionRequest $request): Response
    {
        $this->parent = $warehouse;
        $this->initialisation($request);
        return $this->handle();
    }
}
