<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 04 Apr 2024 10:12:27 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\UI;

use App\Actions\GrpAction;
use App\Actions\UI\Grp\Dashboard\ShowDashboard;
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
                                'number' => $this->group->supplyChainStats->number_agents
                            ],
                        ],
                        [
                            'name'  => __('suppliers'),
                            'icon'  => ['fal', 'fa-person-dolly'],
                            'href'  => ['name'=> 'grp.supply-chain.suppliers.index'],
                            'index' => [
                                'number' => $this->group->supplyChainStats->number_suppliers
                            ],

                        ],
                        [
                            'name'      => __('supplier products'),
                            'shortName' => __('products'),
                            'icon'      => ['fal', 'fa-box-usd'],
                            'href'      => ['name' => 'grp.supply-chain.supplier_products.index'],
                            'index'     => [
                                'number' => $this->group->supplyChainStats->number_supplier_products
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
