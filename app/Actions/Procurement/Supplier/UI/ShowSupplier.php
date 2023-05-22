<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 14:15:00 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Supplier\UI;

use App\Actions\InertiaAction;
use App\Actions\Procurement\Agent\UI\ShowAgent;
use App\Actions\Procurement\PurchaseOrder\UI\IndexPurchaseOrders;
use App\Actions\Procurement\SupplierProduct\UI\IndexSupplierProducts;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Enums\UI\SupplierTabsEnum;
use App\Http\Resources\Procurement\PurchaseOrderResource;
use App\Http\Resources\Procurement\SupplierProductResource;
use App\Http\Resources\Procurement\SupplierResource;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Supplier $supplier
 */
class ShowSupplier extends InertiaAction
{
    public function handle(Supplier $supplier): Supplier
    {
        return $supplier;
    }


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('procurement.suppliers.edit');

        return $request->user()->hasPermissionTo("procurement.view");
    }

    public function asController(Supplier $supplier, ActionRequest $request): Supplier
    {
        $this->initialisation($request)->withTab(SupplierTabsEnum::values());

        return $this->handle($supplier);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inAgent(Agent $agent, Supplier $supplier, ActionRequest $request): Supplier
    {
        $this->initialisation($request)->withTab(SupplierTabsEnum::values());

        return $this->handle($supplier);
    }

    public function htmlResponse(Supplier $supplier, ActionRequest $request): Response
    {

        return Inertia::render(
            'Procurement/Supplier',
            [
                'title'                                    => __('supplier'),
                'breadcrumbs'                              => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'pageHead'                                 => [
                    'icon'          =>
                        [
                            'icon'  => ['fal', 'person-dolly'],
                            'title' => __('agent')
                        ],
                    'title' => $supplier->name,
                ],
                'tabs'                                     => [
                    'current'    => $this->tab,
                    'navigation' => SupplierTabsEnum::navigation()
                ],
                SupplierTabsEnum::SUPPLIER_PRODUCTS->value => $this->tab == SupplierTabsEnum::SUPPLIER_PRODUCTS->value ?
                    fn () => SupplierProductResource::collection(IndexSupplierProducts::run($this->supplier))
                    : Inertia::lazy(fn () => SupplierProductResource::collection(IndexSupplierProducts::run($this->supplier))),

                SupplierTabsEnum::PURCHASE_ORDERS->value => $this->tab == SupplierTabsEnum::PURCHASE_ORDERS->value ?
                    fn () => PurchaseOrderResource::collection(IndexPurchaseOrders::run())
                    : Inertia::lazy(fn () => PurchaseOrderResource::collection(IndexPurchaseOrders::run())),

            ]
        )->table(IndexSupplierProducts::make()->tableStructure($supplier))
            ->table(IndexPurchaseOrders::make()->tableStructure($supplier));
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = ''): array
    {
        $headCrumb = function (supplier $supplier, array $routeParameters, string $suffix) {
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
                            'label' => $supplier->name,
                        ],

                    ],
                    'suffix'=> $suffix

                ],
            ];
        };

        return match ($routeName) {
            'procurement.suppliers.show' =>

            array_merge(
                ProcurementDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    $routeParameters['supplier'],
                    [
                        'index' => [
                            'name'       => 'procurement.suppliers.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'procurement.suppliers.show',
                            'parameters' => [$routeParameters['supplier']->slug]
                        ]
                    ],
                    $suffix
                ),
            ),
            'procurement.agents.show.suppliers.show' =>
            array_merge(
                (new ShowAgent())->getBreadcrumbs(
                    'procurement.agent',
                    ['agent'=> $routeParameters['agent']]
                ),
                $headCrumb(
                    $routeParameters['supplier'],
                    [
                        'index' => [
                            'name'       => 'procurement.agents.show.suppliers.index',
                            'parameters' => [
                                $routeParameters['agent']->slug,
                            ]
                        ],
                        'model' => [
                            'name'       => 'procurement.agents.show.suppliers.show',
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



   public function jsonResponse(Supplier $supplier): SupplierResource
   {
       return new SupplierResource($supplier);
   }
}
