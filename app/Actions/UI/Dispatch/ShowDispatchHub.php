<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:44:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Dispatch;

use App\Actions\OrgAction;
use App\Actions\UI\Dashboards\ShowGroupDashboard;
use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowDispatchHub extends OrgAction
{
    public function handle(Warehouse $warehouse): Warehouse
    {
        return $warehouse;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->authTo("dispatching.{$this->warehouse->id}.view");
    }

    public function asController(Organisation $organisation, Warehouse $warehouse): Warehouse
    {
        $this->initialisationFromWarehouse($warehouse, []);

        return $this->handle($warehouse);
    }


    public function htmlResponse(Warehouse $warehouse, ActionRequest $request): Response
    {
        if ($this->organisation->type == OrganisationTypeEnum::SHOP) {
            $stats = $this->shopOrganisationStats($warehouse, $request);
        } else {
            $stats = $this->agentOrganisationStats($warehouse, $request);
        }


        return Inertia::render(
            'Org/Dispatching/DispatchHub',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'title'       => 'dispatch',
                'pageHead'    => [
                    'icon'  => [
                        'icon'  => ['fal', 'fa-conveyor-belt-alt'],
                        'title' => __('locations')
                    ],
                    'title' => __('Dispatching backlog'),
                ],
                'box_stats'   => $stats

            ]
        );
    }

    public function agentOrganisationStats(Warehouse $warehouse, ActionRequest $request): array
    {
        return [];
    }

    public function shopOrganisationStats(Warehouse $warehouse, ActionRequest $request): array
    {
        return [
            [
                'name'  => __('Delivery Notes'),
                'value' => $warehouse->organisation->orderingStats->number_delivery_notes,
                'route' => [
                    'name'       => 'grp.org.warehouses.show.dispatching.delivery-notes',
                    'parameters' => $request->route()->originalParameters()
                ],
                'icon'  => [
                    'icon'    => 'fal fa-truck',
                    'tooltip' => __('Delivery Notes')
                ]
            ],
            [
                'name'  => __('Fulfilment Returns'),
                'value' => $warehouse->stats->number_pallet_returns_state_picking,
                'route' => [
                    'name'       => 'grp.org.warehouses.show.dispatching.pallet-returns.index',
                    'parameters' => $request->route()->originalParameters()
                ],
                'icon'  => [
                    'icon'    => 'fal fa-sign-out',
                    'tooltip' => __('Fulfilment Returns')
                ]
            ],
        ];
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
