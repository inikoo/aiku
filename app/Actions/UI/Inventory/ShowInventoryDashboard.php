<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 16:34:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Inventory;

use App\Actions\OrgAction;
use App\Actions\UI\Grp\Dashboard\ShowDashboard;
use App\Actions\UI\WithInertia;
use App\Models\SysAdmin\Organisation;
use App\Enums\Inventory\OrgStock\OrgStockStateEnum;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowInventoryDashboard extends OrgAction
{
    use AsAction;
    use WithInertia;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("inventory.{$this->organisation->id}.view");
    }


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
                'breadcrumbs'  => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'        => __('inventory'),
                'pageHead'     => [
                    'title' => __('inventory'),
                ],
                'flatTreeMaps' => [
                    [
                        [
                            'name'  => __('SKUs families'),
                            'icon'  => ['fal', 'fa-boxes-alt'],
                            'href'  => [
                                'name'       => 'grp.org.inventory.org-stock-families.index',
                                'parameters' => $routeParameters
                            ],
                            'index' => [
                                'number' => $this->organisation->inventoryStats->number_stock_families
                            ]

                        ],
                        [
                            'name'  => 'SKUs',
                            'icon'  => ['fal', 'fa-box'],
                            'href'  => [
                                'name'       => 'grp.org.inventory.org-stocks.index',
                                'parameters' => $routeParameters
                            ],
                            'index' => [
                                'number' => $this->organisation->inventoryStats->number_stocks
                            ]

                        ]
                    ]
                ],
                'dashboardStats'     => $this->getDashboardStats(),

            ]
        );
    }

    public function getDashboardStats(): array
    {
        $stats = [];

        $stats['stock'] = [
            'label' => __('Stocks'),
            'count' => $this->organisation->inventoryStats->number_stocks
        ];

        foreach (OrgStockStateEnum::cases() as $case) {
            $stats['stock']['cases'][$case->value] = [
                'value' => $case->value,
                'icon'  => OrgStockStateEnum::stateIcon()[$case->value],
                'count' => OrgStockStateEnum::count($this->organisation)[$case->value],
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
                            'label' => __('inventory'),
                        ]
                    ]
                ]
            );
    }
}
