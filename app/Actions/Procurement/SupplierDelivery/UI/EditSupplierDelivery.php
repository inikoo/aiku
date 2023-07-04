<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\SupplierDelivery\UI;

use App\Actions\InertiaAction;
use App\Models\Procurement\SupplierDelivery;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditSupplierDelivery extends InertiaAction
{
    public function handle(SupplierDelivery $supplierDelivery): SupplierDelivery
    {
        return $supplierDelivery;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('procurement.edit');
        return $request->user()->hasPermissionTo("procurement.view");
    }

    public function asController(SupplierDelivery $supplierDelivery, ActionRequest $request): SupplierDelivery
    {
        $this->initialisation($request);

        return $this->handle($supplierDelivery);
    }



    public function htmlResponse(SupplierDelivery $supplierDelivery): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('supplier delivery'),
                'pageHead'    => [
                    'title'     => $supplierDelivery->number,
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
                                'number' => [
                                    'type'  => 'input',
                                    'label' => __('number'),
                                    'value' => $supplierDelivery->number
                                ],
                            ]
                        ]

                    ],
                    'args' => [
                        'updateRoute' => [
                            'name'      => 'models.supplier-delivery.update',
                            'parameters'=> $supplierDelivery->slug

                        ],
                    ]
                ]
            ]
        );
    }
}
