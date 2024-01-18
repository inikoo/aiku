<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:47:05 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\SupplyChain;

use App\Actions\GrpAction;
use App\Actions\UI\Dashboard\ShowDashboard;
use App\Actions\UI\WithInertia;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowSupplyChainDashboard extends GrpAction
{
    use AsAction;
    use WithInertia;


    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("supply-chain.{$this->group->id}.view");
    }


    public function asController(ActionRequest $request): void
    {
        $this->initialisation(app('group'), $request);
    }


    public function htmlResponse(): Response
    {
        $this->validateAttributes();

        return Inertia::render(
            'SupplyChain/SupplyChainDashboard',
            [
                'breadcrumbs'  => $this->getBreadcrumbs(),
                'title'        => __('supply chain'),
                'pageHead'     => [
                    'title' => __('supply chain'),
                ],
                'flatTreeMaps' => [

                    [

                        [
                            'name'  => __('agents'),
                            'icon'  => ['fal', 'fa-people-arrows'],
                            'href'  => [
                                'name' => 'grp.supply-chain.agents.index'
                            ],
                            'index' => [
                                'number' => $this->group->procurementStats->number_agents
                            ],
                        ],
                        [
                            'name'  => __('suppliers'),
                            'icon'  => ['fal', 'fa-person-dolly'],
                            'href'  => ['name'=> 'grp.supply-chain.suppliers.index'],
                            'index' => [
                                'number' => $this->group->procurementStats->number_suppliers
                            ],

                        ],
                        [
                            'name'      => __('supplier products'),
                            'shortName' => __('products'),
                            'icon'      => ['fal', 'fa-box-usd'],
                            'href'      => ['name' => 'grp.supply-chain.supplier-products.index'],
                            'index'     => [
                                'number' => $this->group->procurementStats->number_supplier_products
                            ],

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
                                'name' => 'grp.supply-chain.dashboard'
                            ],
                            'label' => __('supply chain'),
                        ]
                    ]
                ]
            );
    }


}
