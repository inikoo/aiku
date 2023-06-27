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

class CreateWarehouseAreas extends InertiaAction
{
    public function handle(ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModelBySpreadSheet',
            [
                'title'     => __('Create warehouse areas'),
                'documentName'     => 'inventory',
                'pageHead'  => [
                    'title' => __('Create warehouse areas'),
                    'exitEdit' => [
                        'label' => __('Back'),
                        'route' => [
                            'name'       => 'inventory.warehouse-areas.index',
                            'parameters' => array_values($this->originalParameters)
                        ],
                    ],
                    'clearMulti' => [
                        'route' => [
                            'name'       => 'shops.create-multi-clear',
                            'parameters' => array_values($this->originalParameters)
                        ],
                    ]
                ],
                'sheetData' => [
                    'columns' => [
                        [
                            'id'             => 'code',
                            'name'           => __('Code'),
                            'columnType'     => 'string',
                            'prop'           => 'code',
                            'required'       => true,
                        ],
                        [
                            'id'             => 'name',
                            'name'           => __('Label'),
                            'columnType'     => 'string',
                            'prop'           => 'name',
                            'required'       => true,
                        ],
                    ],
                ],
                'saveRoute' => [
                    'name' => 'models.shop.store-multi',
                ]
            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('inventory.warehouse-areas.edit');
    }


    public function asController(Warehouse $warehouse, ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle($request);
    }
}
