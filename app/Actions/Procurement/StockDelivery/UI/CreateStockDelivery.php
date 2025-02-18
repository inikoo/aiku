<?php

/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 10 May 2023 09:21:57 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\StockDelivery\UI;

use App\Actions\OrgAction;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateStockDelivery extends OrgAction
{
    public function handle(ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('new supplier delivery'),
                'pageHead'    => [
                    'title'        => __('new supplier delivery'),
                    'cancelCreate' => [
                        'route' => [
                            'name'       => 'grp.org.procurement.stock_deliveries.index',
                            'parameters' => array_values($request->route()->originalParameters())
                        ],
                    ]

                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('Create supplier delivery'),
                            'fields' => [

                                'number' => [
                                    'type'  => 'input',
                                    'label' => __('number'),
                                    'value' => ''
                                ],
                                'date' => [
                                    'type'  => 'date',
                                    'label' => __('date'),
                                    'value' => ''
                                ],
                            ]
                        ]
                    ],
                    'route'      => [
                        'name'       => 'grp.models.supplier-delivery.store',
                    ]
                ],


            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->authTo('procurement.edit');
    }


    public function asController(Organisation $organisation, ActionRequest $request): Response
    {
        $this->initialisation($organisation, $request);

        return $this->handle($request);
    }

    public function getBreadcrumbs(): array
    {
        return array_merge(
            IndexStockDeliveries::make()->getBreadcrumbs(),
            [
                [
                    'type'         => 'creatingModel',
                    'creatingModel' => [
                        'label' => __('Creating stock deliveries'),
                    ]
                ]
            ]
        );
    }
}
