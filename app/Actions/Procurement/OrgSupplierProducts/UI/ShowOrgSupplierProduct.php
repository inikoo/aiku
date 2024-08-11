<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 28 May 2024 12:06:23 British Summer Time, Plane Manchester-Malaga
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplierProducts\UI;

use App\Actions\Helpers\History\IndexHistory;
use App\Actions\OrgAction;
use App\Actions\Procurement\OrgAgent\UI\GetOrgAgentShowcase;
use App\Actions\Procurement\PurchaseOrder\UI\IndexPurchaseOrders;
use App\Actions\Procurement\UI\ShowProcurementDashboard;
use App\Enums\UI\SupplyChain\SupplierProductTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\Procurement\PurchaseOrderResource;
use App\Http\Resources\SupplyChain\SupplierProductResource;
use App\Http\Resources\SupplyChain\SupplierResource;
use App\Models\Procurement\OrgAgent;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\SupplierProduct;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowOrgSupplierProduct extends OrgAction
{
    public function handle(SupplierProduct $supplierProduct): SupplierProduct
    {
        return $supplierProduct;
    }


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("procurement.{$this->organisation->id}.edit");

        return $request->user()->hasPermissionTo("procurement.{$this->organisation->id}.view");
    }

    public function asController(Organisation $organisation, SupplierProduct $supplierProduct, ActionRequest $request): SupplierProduct
    {
        $this->initialisation($organisation, $request);

        return $this->handle($supplierProduct);
    }

    public function inOrgAgent(Organisation $organisation, OrgAgent $orgAgent, SupplierProduct $supplierProduct, ActionRequest $request): SupplierProduct
    {
        $this->initialisation($organisation, $request);

        return $this->handle($supplierProduct);
    }

    public function htmlResponse(SupplierProduct $supplierProduct, ActionRequest $request): Response
    {
        return Inertia::render(
            'Procurement/OrgSupplierProduct',
            [
                'title'                                           => __('supplier product'),
                'breadcrumbs'                                     => $this->getBreadcrumbs($supplierProduct),
                'navigation'                                      => [
                    'previous' => $this->getPrevious($supplierProduct, $request),
                    'next'     => $this->getNext($supplierProduct, $request),
                ],
                'pageHead'                                        => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'box-usd'],
                            'title' => __('agent')
                        ],
                    'title' => $supplierProduct->name,
                    /*
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                            'parameters' => array_values($request->route()->originalParameters())
                        ]
                    ] : false,
                    */
                ],
                'supplier'                                        => new SupplierProductResource($supplierProduct),
                'tabs'                                            => [
                    'current'    => $this->tab,
                    'navigation' => SupplierProductTabsEnum::navigation()
                ],
                SupplierProductTabsEnum::SHOWCASE->value          => $this->tab == SupplierProductTabsEnum::SHOWCASE->value ?
                    fn () => GetOrgAgentShowcase::run($supplierProduct)
                    : Inertia::lazy(fn () => GetOrgAgentShowcase::run($supplierProduct)),
                SupplierProductTabsEnum::SUPPLIER_PRODUCTS->value => $this->tab == SupplierProductTabsEnum::SUPPLIER_PRODUCTS->value ?
                    fn () => SupplierProductResource::collection(IndexOrgSupplierProducts::run($supplierProduct))
                    : Inertia::lazy(fn () => SupplierProductResource::collection(IndexOrgSupplierProducts::run($supplierProduct))),

                SupplierProductTabsEnum::PURCHASE_ORDERS->value => $this->tab == SupplierProductTabsEnum::PURCHASE_ORDERS->value ?
                    fn () => PurchaseOrderResource::collection(IndexPurchaseOrders::run($supplierProduct))
                    : Inertia::lazy(fn () => PurchaseOrderResource::collection(IndexPurchaseOrders::run($supplierProduct))),

                SupplierProductTabsEnum::HISTORY->value => $this->tab == SupplierProductTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($supplierProduct))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($supplierProduct)))

            ]
        )->table(IndexOrgSupplierProducts::make()->tableStructure())
            ->table(IndexPurchaseOrders::make()->tableStructure())
            ->table(IndexHistory::make()->tableStructure(prefix: SupplierProductTabsEnum::HISTORY->value));
    }


    public function jsonResponse(Supplier $supplier): SupplierResource
    {
        return new SupplierResource($supplier);
    }

    public function getBreadcrumbs(SupplierProduct $supplierProduct, $suffix = null): array
    {
        return array_merge(
            (new ShowProcurementDashboard())->getBreadcrumbs(),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name' => 'grp.org.procurement.org_supplier_products.index',
                            ],
                            'label' => __('supplierProduct')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'grp.org.procurement.org_supplier_products.show',
                                'parameters' => [$supplierProduct->slug]
                            ],
                            'label' => $supplierProduct->name,
                        ],
                    ],
                    'suffix'         => $suffix,

                ],
            ]
        );
    }

    public function getPrevious(SupplierProduct $supplierProduct, ActionRequest $request): ?array
    {
        $query = SupplierProduct::where('code', '<', $supplierProduct->code);

        $query = match ($request->route()->getName()) {
            'grp.org.procurement.org_agents.show.org_supplier_products.show' => $query->where('supplier_products.agent_id', $request->route()->originalParameters()['agent']->id),
            'grp.org.procurement.org_agents.show.show.supplier.org_supplier_products.show',
            'grp.procurement.supplier.org_supplier_products.show' => $query->where('supplier_products.supplier_id', $request->route()->originalParameters()['supplier']->id),

            default => $query
        };

        $previous = $query->orderBy('code', 'desc')->first();


        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(SupplierProduct $supplierProduct, ActionRequest $request): ?array
    {
        $query = SupplierProduct::where('code', '>', $supplierProduct->code);

        $query = match ($request->route()->getName()) {
            'grp.org.procurement.org_agents.show.org_supplier_products.show' => $query->where('supplier_products.agent_id', $request->route()->originalParameters()['agent']->id),
            'grp.org.procurement.org_agents.show.show.supplier.org_supplier_products.show',
            'grp.procurement.supplier.org_supplier_products.show' => $query->where('supplier_products.supplier_id', $request->route()->originalParameters()['supplier']->id),

            default => $query
        };

        $next = $query->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?SupplierProduct $supplierProduct, string $routeName): ?array
    {
        if (!$supplierProduct) {
            return null;
        }

        return match ($routeName) {
            'grp.org.procurement.org_supplier_products.show' => [
                'label' => $supplierProduct->code,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'supplierProduct' => $supplierProduct->slug
                    ]

                ]
            ],
            'grp.org.procurement.org_agents.show.org_supplier_products.show' => [
                'label' => $supplierProduct->code,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'agent'           => $supplierProduct->agent->slug,
                        'supplierProduct' => $supplierProduct->slug
                    ]

                ]
            ]
        };
    }
}
