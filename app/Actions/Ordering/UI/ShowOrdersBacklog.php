<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:40:27 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\UI;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\UI\ShowOrganisationDashboard;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowOrdersBacklog extends OrgAction
{
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("orders.{$this->organisation->id}.view");
    }


    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): Shop
    {
        $this->initialisationFromShop($shop, $request);

        return $shop;
    }

    public function inOrganisation(Organisation $organisation, ActionRequest $request): Organisation
    {
        $this->initialisation($organisation, $request);

        return $organisation;
    }


    public function htmlResponse(Organisation|Shop $parent, ActionRequest $request): Response
    {

        return Inertia::render(
            'Ordering/OrdersBacklog',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $parent,
                    $request->route()->originalParameters()
                ),
                'title'       => __('orders backlog'),
                'pageHead'    => [
                    'title'     => __('orders backlog'),

                ],

                'tabs_box' => [
                    [
                        'label' => __('In basket'),
                        'tabs' => [
                            [
                                'tab_slug' => 'showcase',
                                'label' => 12456,
                                'type' => 'number',
                                'icon' => 'fal fa-tachometer-alt',
                                'information' => [
                                    'label' => 999999998,
                                    'type' => 'number'
                                ]
                            ]
                        ]
                    ],
                    [
                        'label' => __('Submitted'),
                        'tabs' => [
                            [
                                'tab_slug' => 'submitted_not_paid',
                                'label' => '999999999',
                                'type' => 'currency',
                                'information' => [
                                    'label' => '777777777777777',
                                    'type' => 'currency'
                                ]
                            ],
                            [
                                'tab_slug' => 'submitted',
                                'label' => '999999999',
                                'type' => 'currency',
                                'information' => [
                                    'label' => '777777777777777',
                                    'type' => 'currency'
                                ]
                            ],
                        ]
                    ],
                    [
                        'label' => __('In warehouse'),
                        'tabs' => [
                            [
                                'tab_slug' => 'in_warehouse',
                                'label' => '77777777',
                                'type'  => 'number',
                                'information' => [
                                    'label' => '99999999999999',
                                    'type'  => 'number',
                                ]
                            ],
                        ]
                    ],
                    [
                        'label' => __('Invoicing'),
                        'tabs' => [
                            [
                                'tab_slug' => 'packed',
                                'label' => 99999,
                                'icon' => 'fal fa-box',
                                'indicator' => true,
                                'iconClass' => 'text-teal-500',
                                'information' => [
                                    'label' => 'Info 1',
                                    'type' => 'icon'
                                ]
                            ],
                            [
                                'tab_slug' => 'packed_done',
                                'label' => 777777777,
                                'icon' => 'fal fa-box-check',
                                'iconClass' => 'text-orange-500',
                                'information' => [
                                    'label' => 'Info 2',
                                    'type' => 'icon'
                                ]
                            ],
                            [
                                'tab_slug' => 'images',
                                'label' => 88888888,
                                'icon' => 'fal fa-file-invoice',
                                'iconClass' => 'text-orange-500',
                                'information' => [
                                    'label' => 'Info 2',
                                    'type' => 'icon'
                                ]
                            ]
                        ]
                    ],
                    [
                        'label' => __('Dispatched today'),
                        'tabs' => [
                            [
                                'tab_slug' => 'dispatched_today',
                                'label' => '12313',
                                'type'  => 'number',
                                'information' => [
                                    'label' => '000000000000',
                                    'type' => 'currency'
                                ]
                            ],
                        ]
                    ]
                ],
            ]
        );
    }

    public function getBreadcrumbs(Organisation|Shop $parent, array $routeParameters): array
    {
        return match (class_basename($parent)) {
            'Shop' =>
            array_merge(
                ShowShop::make()->getBreadcrumbs($routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.shops.show.ordering.backlog',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('Orders backlog')
                        ]
                    ]
                ]
            ),
            default =>
            array_merge(
                ShowOrganisationDashboard::make()->getBreadcrumbs(Arr::only($routeParameters, 'organisation')),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.ordering.backlog',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('Orders backlog').' ('.__('all shops').')',
                        ]
                    ]
                ]
            )
        };
    }

}
