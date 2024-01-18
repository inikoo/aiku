<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:47:05 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Procurement;

use App\Actions\OrgAction;
use App\Actions\UI\Dashboard\ShowDashboard;
use App\Actions\UI\WithInertia;
use App\Models\SysAdmin\Organisation;
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


    public function asController(Organisation $organisation, ActionRequest $request): void
    {
        $this->initialisation($organisation, $request);

    }


    public function htmlResponse(): Response
    {
        $this->validateAttributes();

        return Inertia::render(
            'Procurement/ProcurementDashboard',
            [
                'breadcrumbs'  => $this->getBreadcrumbs(),
                'title'        => __('procurement'),
                'pageHead'     => [
                    'title' => __('procurement'),
                ],
                'flatTreeMaps' => [

                    [
                        [
                            'name'         => __('agents'),
                            'icon'         => ['fal', 'fa-people-arrows'],
                            'href'         => ['grp.procurement.agents.index'],
                            'index'        => [
                                'number' => $this->organisation->procurementStats->number_agents
                            ],
                            'rightSubLink' => [
                                'tooltip'    => __('marketplace agents'),
                                'icon'       => ['fal', 'fa-store'],
                                'labelStyle' => 'bordered',
                                'href'       => ['grp.procurement.marketplace.agents.index'],

                            ]

                        ],
                        [
                            'name'         => __('suppliers'),
                            'icon'         => ['fal', 'fa-person-dolly'],
                            'href'         => ['grp.procurement.suppliers.index'],
                            'index'        => [
                                'number' => $this->organisation->procurementStats->number_suppliers
                            ],
                            'rightSubLink' => [
                                'tooltip'    => __('marketplace suppliers'),
                                'icon'       => ['fal', 'fa-store'],
                                'labelStyle' => 'bordered',
                                'href'       => ['grp.procurement.marketplace.suppliers.index'],

                            ]

                        ],
                        [
                            'name'         => __('supplier products'),
                            'shortName'    => __('products'),
                            'icon'         => ['fal', 'fa-box-usd'],
                            'href'         => ['grp.procurement.supplier-products.index'],
                            'index'        => [
                                'number' => $this->organisation->procurementStats->number_supplier_products
                            ],
                            'rightSubLink' => [
                                'tooltip'    => __('marketplace suppliers'),
                                'icon'       => ['fal', 'fa-store'],
                                'labelStyle' => 'bordered',
                                'href'       => ['grp.procurement.marketplace.supplier-products.index'],

                            ]

                        ],
                    ],

                    [
                        [
                            'name'  => __('purchase orders'),
                            'icon'  => ['fal', 'fa-clipboard-list'],
                            'href'  => ['grp.procurement.purchase-orders.index'],
                            'index' => [
                                'number' => $this->organisation->procurementStats->number_purchase_orders
                            ]

                        ],
                        [
                            'name'  => __('supplier deliveries'),
                            'icon'  => ['fal', 'fa-truck-container'],
                            'href'  => ['grp.procurement.supplier-deliveries.index'],
                            'index' => [
                                'number' => $this->organisation->procurementStats->number_deliveries
                            ]

                        ],
                    ],
                ]

            ]
        );
    }

    public function getBreadcrumbs(): array
    {
        return
            array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name' => 'grp.procurement.dashboard'
                            ],
                            'label' => __('procurement'),
                        ]
                    ]
                ]
            );
    }


}
