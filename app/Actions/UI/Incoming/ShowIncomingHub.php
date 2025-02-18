<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Aug 2024 12:57:59 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Incoming;

use App\Actions\OrgAction;
use App\Actions\UI\Dashboards\ShowGroupDashboard;
use App\Actions\UI\WithInertia;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowIncomingHub extends OrgAction
{
    use AsAction;
    use WithInertia;

    public function handle($scope)
    {
        return $scope;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->authTo("incoming.{$this->organisation->id}.view");
    }

    public function asController(Organisation $organisation, Warehouse $warehouse): Organisation
    {
        $this->initialisationFromWarehouse($warehouse, []);
        return $this->handle($organisation);
    }


    public function htmlResponse(Organisation $scope, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Incoming/IncomingHub',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'title'       => 'incoming',
                'pageHead'    => [
                    'icon'  => [
                        'icon'  => ['fal', 'fa-arrow-to-bottom'],
                        'title' => __('incoming')
                    ],
                    'model' => __('Goods in'),
                    'title' => __('Incoming Hub'),
                ],
                'box_stats' => [
                    [
                        'name' => __('Stock Deliveries'),
                        'value' => $scope->procurementStats->number_stock_deliveries,
                        'route' => [
                            'name'       => 'grp.org.warehouses.show.incoming.stock_deliveries.index',
                            'parameters' => $request->route()->originalParameters()
                        ],
                        'icon'  => [
                            'icon' => 'fal fa-truck-container',
                            'tooltip' => __('Stock Deliveries')
                        ]
                    ],
                    [
                        'name' => __('Fulfilment Deliveries'),
                        'value' => $scope->fulfilmentStats->number_pallet_deliveries,
                        'route' => [
                            'name'       => 'grp.org.warehouses.show.incoming.pallet_deliveries.index',
                            'parameters' => $request->route()->originalParameters()
                        ],
                        'icon'  => [
                            'icon'  => 'fal fa-truck-couch',
                            'tooltip' => __('Fulfilment Deliveries')
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
                            'name'       => 'grp.org.warehouses.show.incoming.backlog',
                            'parameters' => $routeParameters
                        ],
                        'label' => __('Goods in'),
                    ]
                ]
            ]
        );
    }

}
