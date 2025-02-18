<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:44:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Dispatch;

use App\Actions\OrgAction;
use App\Actions\UI\Dashboards\ShowGroupDashboard;
use App\Actions\UI\WithInertia;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowDispatchHub extends OrgAction
{
    use AsAction;
    use WithInertia;

    public function handle($scope)
    {
        return $scope;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->authTo("dispatching.{$this->organisation->id}.view");
    }

    public function asController(Organisation $organisation, Warehouse $warehouse): Organisation
    {
        $this->initialisationFromWarehouse($warehouse, []);
        return $this->handle($organisation);
    }




    public function htmlResponse(Organisation $scope, ActionRequest $request): Response
    {



        return Inertia::render(
            'Org/Dispatching/DispatchHub',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'title'       => 'dispatch',
                'pageHead'    => [
                    'icon'      => [
                        'icon'  => ['fal', 'fa-conveyor-belt-alt'],
                        'title' => __('locations')
                    ],
                    'model'     => __('Goods Out'),
                    'title'     => __('Dispatching backlog'),
                            ],
                'box_stats' => [
                    [
                        'name' => __('Delivery Notes'),
                        'value' => $scope->orderingStats->number_delivery_notes,
                        'route' => [
                            'name'       => 'grp.org.warehouses.show.dispatching.delivery-notes',
                            'parameters' => $request->route()->originalParameters()
                        ],
                        'icon'  => [
                            'icon'  => 'fal fa-truck',
                            'tooltip' => __('Delivery Notes')
                        ]
                    ],
                    [
                        'name' => __('Fulfilment Returns'),
                        'value' => $scope->fulfilmentStats->number_pallet_returns,
                        'route' => [
                            'name'       => 'grp.org.warehouses.show.dispatching.pallet-returns.index',
                            'parameters' => $request->route()->originalParameters()
                        ],
                        'icon'  => [
                            'icon'  => 'fal fa-sign-out',
                            'tooltip' => __('Fulfilment Returns')
                        ]
                    ],
                ],

            ]
        );
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return array_merge(
            ShowGroupDashboard::make()->getBreadcrumbs(),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.warehouses.show.dispatching.backlog',
                            'parameters' => $routeParameters
                        ],
                        'label' => __('Dispatching'),
                    ]
                ]
            ]
        );
    }

}
