<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 18 May 2023 14:27:30 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Shop\UI;

use App\Actions\InertiaAction;
use App\Actions\Inventory\StockFamily\UI\HasUIStockFamilies;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateShop extends InertiaAction
{
    use HasUIStockFamilies;


    public function handle(): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('new shop'),
                'pageHead'    => [
                    'title'        => __('new shop'),
                    'cancelCreate' => [
                        'route' => [
                            'name'       => 'inventory.stock-families.index',
                            'parameters' => array_values($this->originalParameters)
                        ],
                    ]

                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('create shop'),
                            'fields' => [

                                'code' => [
                                    'type'  => 'input',
                                    'label' => __('code'),
                                    'value' => ''
                                ],
                                'name' => [
                                    'type'  => 'input',
                                    'label' => __('name'),
                                    'value' => ''
                                ],
                                'skus' => [
                                    'type'  => 'input',
                                    'label' => __('skus'),
                                    'value' => ''
                                ],
                            ]
                        ]
                    ],
                    'route'      => [
                        'name'       => 'models.shop.store',
                    ]
                ],

            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('shops');
    }


    public function asController(ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle();
    }
}
