<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\StockFamily\UI;

use App\Actions\InertiaAction;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateStockFamily extends InertiaAction
{
    use HasUIStockFamilies;

    public function handle(): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('new stock family'),
                'pageHead'    => [
                    'title'        => __('new stock family'),
                    'icon'         => [
                        'title' => __("stock's families"),
                        'icon'  => 'fal fa-boxes-alt'
                    ],
                    'actions'      => [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'label' => __('cancel'),
                            'route' => [
                                'name'       => 'inventory.stock-families.index',
                                'parameters' => array_values($this->originalParameters)
                            ],
                        ]
                    ]
                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('new family'),
                            'fields' => [
                                'code' => [
                                    'type'      => 'input',
                                    'label'     => __('code'),
                                    'required'  => true
                                ],
                                'name' => [
                                    'type'      => 'input',
                                    'label'     => __('name'),
                                    'required'  => true
                                ],
                            ]
                        ]
                    ],
                    'route' => [
                        'name' => 'models.stock-family.store',
                    ]
                ],

            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('inventory.stocks.edit');
    }


    public function asController(ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle();
    }

    public function getBreadcrumbs(): array
    {
        return array_merge(
            IndexStockFamilies::make()->getBreadcrumbs(),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __("creating stock family"),
                    ]
                ]
            ]
        );
    }
}
