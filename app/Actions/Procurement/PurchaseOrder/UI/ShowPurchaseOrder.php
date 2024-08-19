<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 13:52:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\PurchaseOrder\UI;

use App\Actions\Helpers\History\IndexHistory;
use App\Actions\OrgAction;
use App\Actions\Procurement\PurchaseOrderItem\UI\IndexPurchaseOrderItems;
use App\Actions\Procurement\UI\ShowProcurementDashboard;
use App\Enums\UI\Procurement\PurchaseOrderTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\Procurement\PurchaseOrderItemResource;
use App\Http\Resources\Procurement\PurchaseOrderResource;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;


class ShowPurchaseOrder extends OrgAction
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo("procurement.{$this->organisation->id}.edit");
        $this->canDelete = $request->user()->hasPermissionTo("procurement.{$this->organisation->id}.edit");

        return $request->user()->hasPermissionTo("procurement.{$this->organisation->id}.view");
    }

    public function asController(Organisation $organisation, PurchaseOrder $purchaseOrder, ActionRequest $request): PurchaseOrder
    {
        $this->initialisation($organisation, $request)->withTab(PurchaseOrderTabsEnum::values());

        return $purchaseOrder;
    }

    public function htmlResponse(PurchaseOrder $purchaseOrder, ActionRequest $request): Response
    {
        $this->validateAttributes();

        return Inertia::render(
            'Procurement/PurchaseOrder',
            [
                'title'       => __('purchase order'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $purchaseOrder,
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'  => [
                    'previous' => $this->getPrevious($purchaseOrder, $request),
                    'next'     => $this->getNext($purchaseOrder, $request),
                ],
                'pageHead'    => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'clipboard-list'],
                            'title' => __('purchase order')
                        ],
                    'title' => $purchaseOrder->reference,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                            'parameters' => array_values($request->route()->originalParameters())
                        ]
                    ] : false,

                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => PurchaseOrderTabsEnum::navigation()
                ],

                PurchaseOrderTabsEnum::SHOWCASE->value => $this->tab == PurchaseOrderTabsEnum::SHOWCASE->value ?
                    fn() => new PurchaseOrderResource(($purchaseOrder))
                    : Inertia::lazy(fn() => new PurchaseOrderResource(($purchaseOrder))),

                PurchaseOrderTabsEnum::ITEMS->value => $this->tab == PurchaseOrderTabsEnum::ITEMS->value ?
                    fn() => PurchaseOrderItemResource::collection(IndexPurchaseOrderItems::run($purchaseOrder))
                    : Inertia::lazy(fn() => PurchaseOrderItemResource::collection(IndexPurchaseOrderItems::run($purchaseOrder))),

                PurchaseOrderTabsEnum::HISTORY->value => $this->tab == PurchaseOrderTabsEnum::HISTORY->value ?
                    fn() => HistoryResource::collection(IndexHistory::run($purchaseOrder))
                    : Inertia::lazy(fn() => HistoryResource::collection(IndexHistory::run($purchaseOrder)))
            ]
        )->table(IndexPurchaseOrderItems::make()->tableStructure())
            ->table(IndexHistory::make()->tableStructure(prefix: PurchaseOrderTabsEnum::HISTORY->value));
    }

    public function jsonResponse(PurchaseOrder $purchaseOrder): PurchaseOrderResource
    {
        return new PurchaseOrderResource($purchaseOrder);
    }

    public function getBreadcrumbs(PurchaseOrder $purchaseOrder, string $routeName, array $routeParameters, $suffix = null): array
    {
        return array_merge(
            (new ShowProcurementDashboard())->getBreadcrumbs($routeParameters),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name'       => 'grp.org.procurement.purchase_orders.index',
                                'parameters' => $routeParameters['organisation']
                            ],
                            'label' => __('Purchase order')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'grp.org.procurement.purchase_orders.show',
                                'parameters' => $routeParameters
                            ],
                            'label' => $purchaseOrder->reference,
                        ],
                    ],
                    'suffix'         => $suffix,

                ],
            ]
        );
    }

    public function getPrevious(PurchaseOrder $purchaseOrder, ActionRequest $request): ?array
    {
        $previous = PurchaseOrder::where('reference', '<', $purchaseOrder->reference)->orderBy('reference', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(PurchaseOrder $purchaseOrder, ActionRequest $request): ?array
    {
        $next = PurchaseOrder::where('reference', '>', $purchaseOrder->reference)->orderBy('reference')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?PurchaseOrder $purchaseOrder, string $routeName): ?array
    {
        if (!$purchaseOrder) {
            return null;
        }

        return match ($routeName) {
            'grp.org.procurement.purchase_orders.show' => [
                'label' => $purchaseOrder->reference,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation'  => $purchaseOrder->organisation,
                        'purchaseOrder' => $purchaseOrder->slug
                    ]

                ]
            ]
        };
    }
}
