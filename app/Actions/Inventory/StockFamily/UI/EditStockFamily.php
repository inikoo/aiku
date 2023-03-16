<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\StockFamily\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\Inventory\StockFamilyResource;
use App\Models\Inventory\StockFamily;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;

class EditStockFamily extends InertiaAction
{
    use HasUIStockFamily;
    public function handle(StockFamily $stockFamily): StockFamily
    {
        return $stockFamily;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('inventory.warehouses.edit');
        return $request->user()->hasPermissionTo("inventory.warehouses.view");
    }

    public function asController(StockFamily $stockFamily, ActionRequest $request): StockFamily
    {
        $this->initialisation($request);

        return $this->handle($stockFamily);
    }



    public function htmlResponse(StockFamily $stockFamily): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('stock family'),
                'breadcrumbs' => $this->getBreadcrumbs($stockFamily),
                'pageHead'    => [
                    'title'     => $stockFamily->code,
                    'exitEdit'  => [
                        'route' => [
                            'name'       => preg_replace('/edit$/', 'show', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ],


                ],

                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('id'),
                            'fields' => [
                                'code' => [
                                    'type'  => 'input',
                                    'label' => __('code'),
                                    'value' => $stockFamily->code
                                ],
                                'name' => [
                                    'type'  => 'input',
                                    'label' => __('label'),
                                    'value' => $stockFamily->name
                                ],
                            ]
                        ]

                    ],
                    'args' => [
                        'updateRoute' => [
                            'name'      => 'models.stock-family.update',
                            'parameters'=> $stockFamily->slug

                        ],
                    ]
                ]
            ]
        );
    }

    #[Pure] public function jsonResponse(StockFamily $stockFamily): StockFamilyResource
    {
        return new StockFamilyResource($stockFamily);
    }
}
