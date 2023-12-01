<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\SupplierPurchaseOrder\UI;

use App\Actions\InertiaAction;
use App\Models\Procurement\PurchaseOrder;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditSupplierPurchaseOrder extends InertiaAction
{
    public function handle(PurchaseOrder $supplierPurchaseOrder): PurchaseOrder
    {
        return $supplierPurchaseOrder;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('procurement.edit');
        return $request->user()->hasPermissionTo("procurement.view");
    }

    public function asController(PurchaseOrder $supplierPurchaseOrder, ActionRequest $request): PurchaseOrder
    {
        $this->initialisation($request);

        return $this->handle($supplierPurchaseOrder);
    }



    public function htmlResponse(PurchaseOrder $supplierPurchaseOrder): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('supplier purchase order'),
                'pageHead'    => [
                    'title'     => $supplierPurchaseOrder->number,
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
                                    'value' => $supplierPurchaseOrder->number
                                ],
                            ]
                        ]

                    ],
                    'args' => [
                        'updateRoute' => [
                            'name'      => 'models.supplier-purchase-order.update',
                            'parameters'=> $supplierPurchaseOrder->slug

                        ],
                    ]
                ]
            ]
        );
    }
}
