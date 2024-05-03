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
        $this->canEdit = $request->user()->hasPermissionTo('procurement.edit');
        return $request->user()->hasPermissionTo("procurement.view");
    }

    public function asController(SupplierDelivery $supplierDelivery, ActionRequest $request): SupplierDelivery
    {
        $this->initialisation($request);

        return $this->handle($supplierDelivery);
    }

    public function htmlResponse(SupplierDelivery $supplierDelivery, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'                                 => __('supplier delivery'),
                'navigation'                            => [
                    'previous' => $this->getPrevious($supplierDelivery, $request),
                    'next'     => $this->getNext($supplierDelivery, $request),
                ],
                'pageHead'    => [
                    'title'     => $supplierDelivery->number,
                    'exitEdit'  => [
                        'route' => [
                            'name'       => preg_replace('/edit$/', 'show', $request->route()->getName()),
                            'parameters' => array_values($request->route()->originalParameters())
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
                            'name'      => 'grp.models.supplier-delivery.update',
                            'parameters'=> $supplierDelivery->slug

                        ],
                    ]
                ]
            ]
        );
    }

    public function getPrevious(SupplierDelivery $supplierDelivery, ActionRequest $request): ?array
    {
        $previous = SupplierDelivery::where('number', '<', $supplierDelivery->number)->orderBy('number', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(SupplierDelivery $supplierDelivery, ActionRequest $request): ?array
    {
        $next = SupplierDelivery::where('number', '>', $supplierDelivery->number)->orderBy('number')->first();
        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?SupplierDelivery $supplierDelivery, string $routeName): ?array
    {
        if(!$supplierDelivery) {
            return null;
        }
        return match ($routeName) {
            'grp.org.procurement.supplier-deliveries.edit'=> [
                'label'=> $supplierDelivery->number,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'employee'=> $supplierDelivery->number
                    ]

                ]
            ]
        };
    }
}
