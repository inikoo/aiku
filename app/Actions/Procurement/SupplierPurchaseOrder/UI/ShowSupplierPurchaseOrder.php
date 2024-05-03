<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 13:52:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\SupplierPurchaseOrder\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Enums\UI\PurchaseOrderTabsEnum;
use App\Http\Resources\Procurement\SupplierDeliveryResource;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\SupplierDelivery;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property SupplierDelivery $supplierDelivery
 */
class ShowSupplierPurchaseOrder extends InertiaAction
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('procurement.edit');

        return $request->user()->hasPermissionTo("procurement.view");
    }

    public function asController(PurchaseOrder $supplierPurchaseOrder, ActionRequest $request): void
    {
        $this->initialisation($request)->withTab(PurchaseOrderTabsEnum::values());
        $this->supplierDelivery    = $supplierPurchaseOrder;
    }

    public function htmlResponse(): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Procurement/SupplierDelivery',
            [
                'title'       => __('supplier purchase order'),
                'breadcrumbs' => $this->getBreadcrumbs($this->supplierDelivery),
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
                    'navigation' => PurchaseOrderTabsEnum::navigation()
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
}
