<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 10 May 2023 09:21:57 Central European Summer, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\SupplierPurchaseOrder\UI;

use App\Actions\InertiaAction;
use App\Actions\Procurement\OrgAgent\UI\HasUIAgents;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateSupplierPurchaseOrder extends InertiaAction
{
    use HasUIAgents;
    public function handle(): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('new supplier purchase order'),
                'pageHead'    => [
                    'title'        => __('new purchase order'),
                    'cancelCreate' => [
                        'route' => [
                            'name'       => 'grp.procurement.supplier-purchase-orders.index',
                            'parameters' => array_values($request->route()->originalParameters())
                        ],
                    ]

                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('create supplier purchase order'),
                            'fields' => [

                                'number' => [
                                    'type'  => 'input',
                                    'label' => __('number'),
                                    'value' => ''
                                ],
                            ]
                        ]
                    ],
                    'route'      => [
                        'name'       => 'grp.models.supplier-purchase-order.update',
                    ]
                ],


            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo('procurement.edit');
    }


    public function asController(ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle();
    }

    public function getBreadcrumbs(): array
    {
        return array_merge(
            IndexSupplierPurchaseOrders::make()->getBreadcrumbs(),
            [
                [
                    'type'         => 'creatingModel',
                    'creatingModel'=> [
                        'label'=> __('creating supplier purchase order'),
                    ]
                ]
            ]
        );
    }
}
