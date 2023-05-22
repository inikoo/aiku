<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 14:15:00 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Marketplace\Supplier\UI;

use App\Actions\InertiaAction;
use App\Actions\Procurement\Marketplace\Agent\UI\ShowMarketplaceAgent;
use App\Actions\UI\Procurement\ProcurementDashboard;
use App\Enums\UI\SupplierTabsEnum;
use App\Http\Resources\Procurement\SupplierResource;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Supplier $marketplaceSupplier
 */
class ShowMarketplaceSupplier extends InertiaAction
{
    public function handle(Supplier $marketplaceSupplier): Supplier
    {
        return $marketplaceSupplier;
    }


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('procurement.edit');

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

    public function htmlResponse(Supplier $marketplaceSupplier, ActionRequest $request): Response
    {


        return Inertia::render(
            'Procurement/MarketplaceSupplier',
            [
                'title'       => __('marketplace supplier'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'pageHead'    => [
                    'title' => $marketplaceSupplier->name,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,

                ],
                'tabs'=> [
                    'current'    => $this->tab,
                    'navigation' => SupplierTabsEnum::navigation()
                ],
            ]
        );
    }


    public function jsonResponse(Supplier $marketplaceSupplier): SupplierResource
    {
        return new SupplierResource($marketplaceSupplier);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = ''): array
    {
        $headCrumb = function (Supplier $supplier, array $routeParameters, string $suffix) {
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
                            'label' => $supplier->code,
                        ],

                    ],
                    'suffix'=> $suffix

                ],
            ];
        };

        return match ($routeName) {
            'procurement.marketplace-suppliers.show' => array_merge(
                (new ProcurementDashboard())->getBreadcrumbs(

                ),
                $headCrumb(
                    $routeParameters['supplier'],
                    [
                        'index' => [
                            'name'       => 'procurement.marketplace-suppliers.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'procurement.marketplace-suppliers.show',
                            'parameters' => [$routeParameters['supplier']->slug]
                        ]
                    ],
                    $suffix
                )
            ),
            'procurement.marketplace-agents.show.suppliers.show' => array_merge(
                (new ShowMarketplaceAgent())->getBreadcrumbs(
                    ['agent'   => $routeParameters['agent']]
                ),
                $headCrumb(
                    $routeParameters['supplier'],
                    [
                        'index' => [
                            'name'       => 'procurement.marketplace-agents.show.suppliers.index',
                            'parameters' => [
                                $routeParameters['agent']->slug
                            ]
                        ],
                        'model' => [
                            'name'       => 'procurement.marketplace-agents.show.suppliers.show',
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

}
