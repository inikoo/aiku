<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 28 May 2024 12:08:07 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\UI;

use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\UI\ShowOrganisationDashboard;
use App\Actions\UI\WithInertia;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ProcurementDashboard extends OrgAction
{
    use AsAction;
    use WithInertia;


    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("procurement.{$this->organisation->id}.view");
    }


    public function asController(Organisation $organisation, ActionRequest $request): ActionRequest
    {
        $this->initialisation($organisation, $request);

        return $request;
    }


    public function htmlResponse(ActionRequest $request): Response
    {
        return Inertia::render(
            'Procurement/ProcurementDashboard',
            [
                'breadcrumbs'  => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'        => __('procurement'),
                'pageHead'     => [
                    'icon'      => [
                        'icon'  => ['fal', 'fa-box-usd'],
                        'title' => __('procurement')
                    ],
                    'title' => __('procurement'),
                ],
                'flatTreeMaps' => [

                    [
                        [
                            'name'         => __('agents'),
                            'icon'         => ['fal', 'fa-people-arrows'],
                            'href'         => [
                                'name'       => 'grp.org.procurement.org_agents.index',
                                'parameters' => ['organisation' => $this->organisation->slug]
                            ],
                            'index'        => [
                                'number' => $this->organisation->procurementStats->number_agents
                            ],
//                            'rightSubLink' => [
//                                'tooltip'    => __('marketplace agents'),
//                                'icon'       => ['fal', 'fa-store'],
//                                'labelStyle' => 'bordered',
//                                'href'       => [
//                                    'name'       => 'grp.org.procurement.marketplace.org_agents.index',
//                                    'parameters' => ['organisation' => $this->organisation->slug]
//                                ],
//
//                            ]

                        ],
                        [
                            'name'         => __('suppliers'),
                            'icon'         => ['fal', 'fa-person-dolly'],
                            'href'         => [
                                'name'       => 'grp.org.procurement.org_suppliers.index',
                                'parameters' => ['organisation' => $this->organisation->slug]
                            ],
                            'index'        => [
                                'number' => $this->organisation->procurementStats->number_suppliers
                            ],
//                            'rightSubLink' => [
//                                'tooltip'    => __('marketplace suppliers'),
//                                'icon'       => ['fal', 'fa-store'],
//                                'labelStyle' => 'bordered',
//                                'href'       => [
//                                    'name'       => 'grp.org.procurement.marketplace.org_suppliers.index',
//                                    'parameters' => ['organisation' => $this->organisation->slug]
//                                ],
//
//                            ]

                        ],
                        [
                            'name'         => __('supplier products'),
                            'shortName'    => __('products'),
                            'icon'         => ['fal', 'fa-box-usd'],
                            'href'         => ['name' => 'grp.org.procurement.org_supplier_products.index', 'parameters' => ['organisation' => $this->organisation->slug]],
                            'index'        => [
                                'number' => $this->organisation->procurementStats->number_supplier_products
                            ],
//                            'rightSubLink' => [
//                                'tooltip'    => __('marketplace suppliers'),
//                                'icon'       => ['fal', 'fa-store'],
//                                'labelStyle' => 'bordered',
//                                'href'       => ['name' => 'grp.org.procurement.marketplace.org_supplier_products.index', 'parameters' => ['organisation' => $this->organisation->slug]],
//
//                            ]

                        ],
                    ],

                    [
                        [
                            'name'  => __('purchase orders'),
                            'icon'  => ['fal', 'fa-clipboard-list'],
                            'href'  => ['name' => 'grp.org.procurement.purchase_orders.index', 'parameters' => ['organisation' => $this->organisation->slug]],
                            'index' => [
                                'number' => $this->organisation->procurementStats->number_purchase_orders
                            ]

                        ],
                        [
                            'name'  => __('supplier deliveries'),
                            'icon'  => ['fal', 'fa-truck-container'],
                            'href'  => ['name' => 'grp.org.procurement.stock_deliveries.index', 'parameters' => ['organisation' => $this->organisation->slug]],
                            'index' => [
                                'number' => $this->organisation->procurementStats->number_deliveries
                            ]

                        ],
                    ],
                ]

            ]
        );
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return
            array_merge(
                ShowOrganisationDashboard::make()->getBreadcrumbs(Arr::only($routeParameters, 'organisation')),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.procurement.dashboard',
                                'parameters' => Arr::only($routeParameters, 'organisation')
                            ],
                            'label' => __('procurement'),
                        ]
                    ]
                ]
            );
    }


}
