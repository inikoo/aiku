<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:44:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Fulfilment;

use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\UI\ShowOrganisationDashboard;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowFulfilmentsDashboard extends OrgAction
{
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("fulfilment.{$this->organisation->id}.view");
    }


    public function asController(Organisation $organisation, ActionRequest $request): ActionRequest
    {
        $this->initialisation($organisation, []);

        return $request;
    }


    public function htmlResponse(ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Fulfilment/FulfilmentsDashboard',
            [
                'breadcrumbs'  => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'        => __('fulfilment'),
                'pageHead'     => [
                    'title' => __('fulfilment central command'),
                ],
                'flatTreeMaps' => [

                    [
                        [
                            'name'  => __('Fulfilment Shops'),
                            'icon'  => ['fal', 'fa-pallets'],
                            'href'  => [
                                'name'       => 'grp.org.fulfilment.shops.index',
                                'parameters' => $request->route()->originalParameters()
                            ],
                            'index' => [
                                'number' => $this->organisation->marketStats->number_shops_type_fulfilment
                            ],

                        ],
                    ],

                    [


                        [
                            'name'  => __('Customers'),
                            'icon'  => ['fal', 'fa-user-tie'],
                            'index' => [
                                'number' => $this->organisation->fulfilmentStats->number_customers_with_stored_items
                            ],

                        ],
                        [
                            'name'  => __('Stored Items'),
                            'icon'  => ['fal', 'fa-narwhal'],
                            'index' => [
                                'number' => $this->organisation->fulfilmentStats->number_stored_items
                            ],

                        ],

                        [
                            'name'  => __('Orders'),
                            'icon'  => ['fal', 'fa-business-time'],
                            'index' => [
                                'number' => $this->organisation->fulfilmentStats->number_customers_with_assets
                            ],

                        ]
                    ]
                ]

            ]
        );
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return
            array_merge(
                ShowOrganisationDashboard::make()->getBreadcrumbs($routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.fulfilment.dashboard',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('fulfilment'),
                            'icon'  => 'fal fa-chart-network'
                        ]
                    ]
                ]
            );
    }


}
