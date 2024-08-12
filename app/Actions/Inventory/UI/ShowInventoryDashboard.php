<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 28 May 2024 16:54:45 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\UI;

use App\Actions\Inventory\HasInventoryAuthorisation;
use App\Actions\OrgAction;
use App\Actions\UI\Grp\Dashboard\ShowDashboard;
use App\Enums\Inventory\OrgStock\OrgStockStateEnum;
use App\Models\SysAdmin\Organisation;
use App\Stubs\Migrations\HasInventoryStats;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowInventoryDashboard extends OrgAction
{
    use HasInventoryStats;
    use HasInventoryAuthorisation;


    public function asController(Organisation $organisation, ActionRequest $request): ActionRequest
    {
        $this->initialisation($organisation, $request);

        return $request;
    }

    public function htmlResponse(ActionRequest $request): Response
    {
        $routeParameters = $request->route()->originalParameters();


        return Inertia::render(
            'Org/Inventory/InventoryDashboard',
            [
                'breadcrumbs'    => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'          => __('Inventory'),
                'pageHead'       => [
                    'title'          => __('Inventory'),
                    'icon'           => [
                        'icon' => 'fal fa-pallet-alt'
                    ],
                    'iconRight' => [
                        'icon'  => ['fal', 'fa-chart-network'],
                        'title' => __('inventory')
                    ],
                ],
                'flatTreeMaps'   => [
                    [
                        [
                            'name'  => __('SKUs families'),
                            'icon'  => ['fal', 'fa-boxes-alt'],
                            'href'  => [
                                'name'       => 'grp.org.inventory.org_stock_families.index',
                                'parameters' => $routeParameters
                            ],
                            'index' => [
                                'number' => $this->organisation->inventoryStats->number_current_org_stock_families
                            ]

                        ],
                        [
                            'name'          => 'SKUs',
                            'icon'          => ['fal', 'fa-box'],
                            'description'   => __('current'),
                            'href'          => [
                                'name'       => 'grp.org.inventory.org_stocks.all_org_stocks.index',
                                'parameters' => $routeParameters
                            ],
                            'index' => [
                                'number' => $this->organisation->inventoryStats->number_current_org_stocks
                            ],
                            'sub_data'  => $this->getDashboardStats()['stock']['cases']
                        ]
                    ]
                ],
                // 'dashboardStats' => $this->getDashboardStats(),

            ]
        );
    }

    public function getDashboardStats(): array
    {
        $stats = [];

        $stats['stock'] = [
            'label' => __('Stocks'),
            'count' => $this->organisation->inventoryStats->number_current_org_stocks
        ];

        foreach (OrgStockStateEnum::cases() as $case) {

            $count=OrgStockStateEnum::count($this->organisation)[$case->value];

            if($case==OrgStockStateEnum::SUSPENDED and $count==0) {
                continue;
            }


            $stats['stock']['cases'][] = [
                'value' => $case->value,
                'icon'  => OrgStockStateEnum::stateIcon()[$case->value],
                'count' => $count,
                'label' => OrgStockStateEnum::labels()[$case->value]
            ];
        }

        return $stats;
    }


    public function getBreadcrumbs(array $routeParameters): array
    {
        return
            array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.inventory.dashboard',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('Inventory'),
                        ]
                    ]
                ]
            );
    }
}
