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
                        'label' => 'Showcase',
                        'tabs' => [
                            [
                                'label' => 12456,
                                'icon' => 'fal fa-tachometer-alt',
                                'indicator' => true,
                                'tab_slug' => 'showcase',
                                'type' => 'number',
                                'information' => [
                                    'label' => 999999998,
                                    'type' => 'number'
                                ]
                            ],
                            [
                                'label' => 'History',
                                'indicator' => false,
                                'tab_slug' => 'history',
                                'type' => 'date',
                                'iconClass' => 'text-green-500',
                                'information' => [
                                    'label' => 'Info 2',
                                    'type' => 'date'
                                ]
                            ]
                        ]
                    ],
                    [
                        'label' => 'Navigation 2',
                        'tabs' => [
                            [
                                'label' => 'Tab 1',
                                'icon' => 'icon-chart',
                                'indicator' => true,
                                'tab_slug' => 'attachments',
                                'type' => 'number',
                                'iconClass' => 'text-red-500',
                                'information' => [
                                    'label' => 'Info 1',
                                    'type' => 'number'
                                ]
                            ]
                        ]
                    ],
                    [
                        'label' => 'Images',
                        'tabs' => [
                            [
                                'label' => 'Avatar',
                                'icon' => 'icon-wallet',
                                'indicator' => false,
                                'tab_slug' => 'images',
                                'iconClass' => 'text-yellow-500',
                                'information' => [
                                    'label' => 'Info 1',
                                ]
                            ],
                        ]
                    ],
                    [
                        'label' => 'Navigation 4',
                        'tabs' => [
                            [
                                'label' => 'Tab 1',
                                'icon' => 'icon-mail',
                                'indicator' => true,
                                'tab_slug' => 'attachments',
                                'type' => 'icon',
                                'iconClass' => 'text-teal-500',
                                'information' => [
                                    'label' => 'Info 1',
                                    'type' => 'icon'
                                ]
                            ],
                            [
                                'label' => 'Tab 2',
                                'icon' => 'icon-phone',
                                'indicator' => false,
                                'tab_slug' => 'images',
                                'type' => 'icon',
                                'iconClass' => 'text-orange-500',
                                'information' => [
                                    'label' => 'Info 2',
                                    'type' => 'icon'
                                ]
                            ]
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
