<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 13:52:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\SupplierDelivery\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Enums\UI\SupplyChain\SupplierDeliveryTabsEnum;
use App\Http\Resources\Procurement\SupplierDeliveryResource;
use App\Models\Procurement\SupplierDelivery;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property SupplierDelivery $supplierDelivery
 */
class ShowSupplierDelivery extends InertiaAction
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('procurement.edit');

        return $request->user()->hasPermissionTo("procurement.view");
    }

    public function asController(SupplierDelivery $supplierDelivery, ActionRequest $request): void
    {
        $this->initialisation($request)->withTab(SupplierDeliveryTabsEnum::values());
        $this->supplierDelivery    = $supplierDelivery;
    }

    public function htmlResponse(ActionRequest $request): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Procurement/SupplierDelivery',
            [
                'title'                                 => __('supplier delivery'),
                'breadcrumbs'                           => $this->getBreadcrumbs($this->supplierDelivery),
                'navigation'                            => [
                    'previous' => $this->getPrevious($this->supplierDelivery, $request),
                    'next'     => $this->getNext($this->supplierDelivery, $request),
                ],
                'pageHead'    => [
                    'icon'  => ['fal', 'people-arrows'],
                    'title' => $this->supplierDelivery->id,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                            'parameters' => array_values($request->route()->originalParameters())
                        ]
                    ] : false,
                ],
                'tabs'=> [
                    'current'    => $this->tab,
                    'navigation' => SupplierDeliveryTabsEnum::navigation()
                ],
            ]
        );
    }


    public function jsonResponse(): SupplierDeliveryResource
    {
        return new SupplierDeliveryResource($this->supplierDelivery);
    }

    public function getBreadcrumbs(SupplierDelivery $supplierDelivery, $suffix = null): array
    {
        return array_merge(
            (new ProcurementDashboard())->getBreadcrumbs(),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name' => 'grp.org.procurement.supplier-deliveries.index',
                            ],
                            'label' => __('supplier delivery')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'grp.org.procurement.supplier-deliveries.show',
                                'parameters' => [$supplierDelivery->slug]
                            ],
                            'label' => $supplierDelivery->number,
                        ],
                    ],
                    'suffix' => $suffix,

                ],
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
            'grp.org.procurement.supplier-deliveries.show'=> [
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
