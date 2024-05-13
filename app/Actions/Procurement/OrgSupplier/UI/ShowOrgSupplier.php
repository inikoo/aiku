<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 May 2024 10:21:46 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplier\UI;

use App\Actions\Helpers\History\IndexHistory;
use App\Actions\OrgAction;
use App\Actions\Procurement\OrgAgent\UI\ShowOrgAgent;
use App\Actions\Procurement\PurchaseOrder\UI\IndexPurchaseOrders;
use App\Actions\Procurement\SupplierDelivery\UI\IndexSupplierDeliveries;
use App\Actions\Procurement\SupplierProduct\UI\IndexSupplierProducts;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Enums\UI\SupplyChain\SupplierTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\Procurement\PurchaseOrderResource;
use App\Http\Resources\Procurement\SupplierDeliveryResource;
use App\Http\Resources\Procurement\SupplierProductResource;
use App\Http\Resources\Procurement\SupplierResource;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\OrgSupplier;
use App\Models\SupplyChain\Supplier;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowOrgSupplier extends OrgAction
{
    public function handle(OrgSupplier $orgSupplier): OrgSupplier
    {
        return $orgSupplier;
    }


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo('procurement.suppliers.edit');
        $this->canDelete = $request->user()->hasPermissionTo('procurement.suppliers.edit');

        return $request->user()->hasPermissionTo("procurement.view");
    }

    public function asController(Organisation $organisation, OrgSupplier $orgSupplier, ActionRequest $request): OrgSupplier
    {
        $this->initialisation($organisation, $request)->withTab(SupplierTabsEnum::values());

        return $this->handle($orgSupplier);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inOrgAgent(Organisation $organisation, OrgAgent $orgAgent, OrgSupplier $orgSupplier, ActionRequest $request): OrgSupplier
    {
        $this->initialisation($organisation, $request)->withTab(SupplierTabsEnum::values());

        return $this->handle($orgSupplier);
    }

    public function htmlResponse(Supplier $orgSupplier, ActionRequest $request): Response
    {
        return Inertia::render(
            'Procurement/Supplier',
            [
                'title'       => __('supplier'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'  => [
                    'previous' => $this->getPrevious($orgSupplier, $request),
                    'next'     => $this->getNext($orgSupplier, $request),
                ],
                'pageHead'    => [
                    'icon'    =>
                        [
                            'icon'  => 'fal fa-person-dolly',
                            'title' => __('supplier')
                        ],
                    'title'   => $orgSupplier->name,
                    'actions' => [
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'edit',
                            'route' => [
                                'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : false,
                        $this->canDelete ? [
                            'type'  => 'button',
                            'style' => 'delete',
                            'route' => [
                                'name'       => 'grp.procurement.suppliers.remove',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : false,
                        $this->canEdit && $orgSupplier->owner_type == 'Organisation' ? [
                            'type'  => 'button',
                            'style' => 'create',
                            'route' => [
                                'name'       => 'grp.procurement.suppliers.show.purchase-orders.create',
                                'parameters' => array_values($request->route()->originalParameters())
                            ],
                            'label' => __('purchase order')
                        ] : false,
                    ],
                    'meta'    => [
                        [
                            'name'     => trans_choice('Purchases|Sales', $orgSupplier->stats->number_open_purchase_orders),
                            'number'   => $orgSupplier->stats->number_open_purchase_orders,
                            'href'     => [
                                'grp.procurement.supplier-products.show',
                                $orgSupplier->slug
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-person-dolly',
                                'tooltip' => __('sales')
                            ]
                        ],
                        [
                            'name'     => trans_choice('product|products', $orgSupplier->stats->number_supplier_products),
                            'number'   => $orgSupplier->stats->number_supplier_products,
                            'href'     => [
                                'grp.procurement.supplier-products.show',
                                $orgSupplier->slug
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-box-usd',
                                'tooltip' => __('products')
                            ]
                        ],
                    ]

                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => SupplierTabsEnum::navigation()
                ],

                SupplierTabsEnum::SHOWCASE->value => $this->tab == SupplierTabsEnum::SHOWCASE->value ?
                    fn () => GetOrgSupplierShowcase::run($orgSupplier)
                    : Inertia::lazy(fn () => GetOrgSupplierShowcase::run($orgSupplier)),

                SupplierTabsEnum::PURCHASES_SALES->value => $this->tab == SupplierTabsEnum::PURCHASES_SALES->value ?
                    fn () => SupplierProductResource::collection(
                        IndexSupplierProducts::run(
                            parent: $orgSupplier,
                            prefix: 'supplier_products'
                        )
                    )
                    : Inertia::lazy(fn () => SupplierProductResource::collection(IndexSupplierProducts::run($orgSupplier))),

                SupplierTabsEnum::SUPPLIER_PRODUCTS->value => $this->tab == SupplierTabsEnum::SUPPLIER_PRODUCTS->value ?
                    fn () => SupplierProductResource::collection(IndexSupplierProducts::run($orgSupplier))
                    : Inertia::lazy(fn () => SupplierProductResource::collection(IndexSupplierProducts::run($orgSupplier))),

                SupplierTabsEnum::PURCHASE_ORDERS->value => $this->tab == SupplierTabsEnum::PURCHASE_ORDERS->value ?
                    fn () => PurchaseOrderResource::collection(IndexPurchaseOrders::run($orgSupplier))
                    : Inertia::lazy(fn () => PurchaseOrderResource::collection(IndexPurchaseOrders::run($orgSupplier))),

                SupplierTabsEnum::DELIVERIES->value => $this->tab == SupplierTabsEnum::DELIVERIES->value ?
                    fn () => SupplierDeliveryResource::collection(IndexSupplierDeliveries::run($orgSupplier))
                    : Inertia::lazy(fn () => SupplierDeliveryResource::collection(IndexSupplierDeliveries::run($orgSupplier))),

                SupplierTabsEnum::HISTORY->value => $this->tab == SupplierTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($orgSupplier))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($orgSupplier)))
            ]
        )->table(IndexSupplierProducts::make()->tableStructure())
            ->table(IndexSupplierProducts::make()->tableStructure())
            ->table(IndexPurchaseOrders::make()->tableStructure())
            ->table(IndexSupplierDeliveries::make()->tableStructure())
            ->table(IndexHistory::make()->tableStructure());
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = ''): array
    {
        $headCrumb = function (Supplier $orgSupplier, array $routeParameters, string $suffix) {
            return [
                [

                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('suppliers')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $orgSupplier->name,
                        ],

                    ],
                    'suffix'         => $suffix

                ],
            ];
        };

        return match ($routeName) {
            'grp.procurement.suppliers.show' =>
            array_merge(
                ProcurementDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    $routeParameters['supplier'],
                    [
                        'index' => [
                            'name'       => 'grp.procurement.suppliers.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'grp.procurement.suppliers.show',
                            'parameters' => [$routeParameters['supplier']->slug]
                        ]
                    ],
                    $suffix
                ),
            ),
            'grp.procurement.agents.show.suppliers.show' =>
            array_merge(
                (new ShowOrgAgent())->getBreadcrumbs(
                    ['agent' => $routeParameters['agent']]
                ),
                $headCrumb(
                    $routeParameters['supplier'],
                    [
                        'index' => [
                            'name'       => 'grp.procurement.agents.show.suppliers.index',
                            'parameters' => [
                                $routeParameters['agent']->slug,
                            ]
                        ],
                        'model' => [
                            'name'       => 'grp.procurement.agents.show.suppliers.show',
                            'parameters' => [
                                $routeParameters['agent']->slug,
                                $routeParameters['supplier']->slug
                            ]
                        ]
                    ],
                    $suffix
                )
            ),
            default => []
        };
    }


    public function jsonResponse(Supplier $orgSupplier): SupplierResource
    {
        return new SupplierResource($orgSupplier);
    }

    public function getPrevious(Supplier $orgSupplier, ActionRequest $request): ?array
    {
        $previous = Supplier::where('code', '<', $orgSupplier->code)->when(true, function ($query) use ($orgSupplier, $request) {
            if ($request->route()->getName() == 'grp.procurement.agents.show.suppliers.show') {
                $query->where('suppliers.agent_id', $orgSupplier->agent_id);
            }
        })->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Supplier $orgSupplier, ActionRequest $request): ?array
    {
        $next = Supplier::where('code', '>', $orgSupplier->code)->when(true, function ($query) use ($orgSupplier, $request) {
            if ($request->route()->getName() == 'grp.procurement.agents.show.suppliers.show') {
                $query->where('suppliers.agent_id', $orgSupplier->agent_id);
            }
        })->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Supplier $orgSupplier, string $routeName): ?array
    {
        if (!$orgSupplier) {
            return null;
        }

        return match ($routeName) {
            'grp.procurement.suppliers.show' => [
                'label' => $orgSupplier->code,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'supplier' => $orgSupplier->slug
                    ]

                ]
            ],
            'grp.procurement.agents.show.suppliers.show' => [
                'label' => $orgSupplier->code,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'agent'    => $orgSupplier->agent->slug,
                        'supplier' => $orgSupplier->slug
                    ]

                ]
            ]
        };
    }

}
