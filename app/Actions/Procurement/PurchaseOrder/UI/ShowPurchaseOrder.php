<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 13:52:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\PurchaseOrder\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Enums\UI\PurchaseOrderTabsEnum;
use App\Http\Resources\Procurement\PurchaseOrderResource;
use App\Models\Procurement\PurchaseOrder;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property PurchaseOrder $purchaseOrder
 */
class ShowPurchaseOrder extends InertiaAction
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('procurement.edit');

        return $request->user()->hasPermissionTo("procurement.view");
    }

    public function asController(PurchaseOrder $purchaseOrder, ActionRequest $request): void
    {
        $this->initialisation($request)->withTab(PurchaseOrderTabsEnum::values());
        $this->purchaseOrder = $purchaseOrder;
    }

    public function htmlResponse(): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Procurement/PurchaseOrder',
            [
                'title'       => __('purchase order'),
                'breadcrumbs' => $this->getBreadcrumbs($this->purchaseOrder),
                'pageHead'    => [
                    'icon'  => 'fal fa-agent',
                    'title' => $this->purchaseOrder,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,

                ],
                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => PurchaseOrderTabsEnum::navigation()
                ],
            ]
        );
    }

    public function jsonResponse(): PurchaseOrderResource
    {
        return new PurchaseOrderResource($this->purchaseOrder);
    }

    public function getBreadcrumbs(PurchaseOrder $purchaseOrder, $suffix = null): array
    {
        return array_merge(
            (new ProcurementDashboard())->getBreadcrumbs(),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name' => 'procurement.purchase-orders.index',
                            ],
                            'label' => __('purchaseOrder')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'procurement.purchase-orders.show',
                                'parameters' => [$purchaseOrder->slug]
                            ],
                            'label' => $purchaseOrder->number,
                        ],
                    ],
                    'suffix' => $suffix,

                ],
            ]
        );
    }
}
