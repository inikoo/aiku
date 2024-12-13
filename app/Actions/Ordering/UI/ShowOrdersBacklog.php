<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:40:27 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\UI;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\Ordering\Order\UI\IndexOrders;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\UI\ShowOrganisationDashboard;
use App\Http\Resources\Ordering\OrdersResource;
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
                        'label' => __('Creating'),
                        'tabs' => [
                            [
                                'tab_slug' => 'creating',
                                'label' => $parent->orderHandlingStats->number_orders_state_creating,
                                'type' => 'number',
                                'icon' => 'fal fa-tachometer-alt',
                                'information' => [
                                    'label' => $parent->orderHandlingStats->orders_state_creating_amount_org_currency,
                                    'type' => 'currency'
                                ]
                            ]
                        ]
                    ],
                    [
                        'label' => __('Submitted'),
                        'tabs' => [
                            [
                                'tab_slug' => 'submitted',
                                'label' => $parent->orderHandlingStats->number_orders_state_submitted,
                                'type' => 'number',
                                'icon' => 'fal fa-tachometer-alt',
                                'information' => [
                                    'label' => $parent->orderHandlingStats->orders_state_submitted_amount_org_currency,
                                    'type' => 'currency'
                                ]
                            ]
                        ]
                    ],
                    [
                        'label' => __('In warehouse'),
                        'tabs' => [
                            [
                                'tab_slug' => 'in_warehouse',
                                'label' => $parent->orderHandlingStats->number_orders_state_in_warehouse,
                                'type' => 'number',
                                'icon' => 'fal fa-tachometer-alt',
                                'information' => [
                                    'label' => $parent->orderHandlingStats->orders_state_in_warehouse_amount_org_currency,
                                    'type' => 'currency'
                                ]
                            ]
                        ]
                    ],
                    [
                        'label' => __('Handling'),
                        'tabs' => [
                            [
                                'tab_slug' => 'handling',
                                'label' => $parent->orderHandlingStats->number_orders_state_handling,
                                'type' => 'number',
                                'icon' => 'fal fa-tachometer-alt',
                                'information' => [
                                    'label' => $parent->orderHandlingStats->orders_state_handling_amount_org_currency,
                                    'type' => 'currency'
                                ]
                            ]
                        ]
                    ],
                    [
                        'label' => __('Handling blocked'),
                        'tabs' => [
                            [
                                'tab_slug' => 'handling_blocked',
                                'label' => $parent->orderHandlingStats->number_orders_state_handling_blocked,
                                'type' => 'number',
                                'icon' => 'fal fa-tachometer-alt',
                                'information' => [
                                    'label' => $parent->orderHandlingStats->orders_state_handling_blocked_amount_org_currency,
                                    'type' => 'currency'
                                ]
                            ]
                        ]
                    ],
                    [
                        'label' => __('Packed'),
                        'tabs' => [
                            [
                                'tab_slug' => 'packed',
                                'label' => $parent->orderHandlingStats->number_orders_state_packed,
                                'type' => 'number',
                                'icon' => 'fal fa-tachometer-alt',
                                'information' => [
                                    'label' => $parent->orderHandlingStats->orders_state_packed_amount_org_currency,
                                    'type' => 'currency'
                                ]
                            ]
                        ]
                    ],
                    [
                        'label' => __('Finalised'),
                        'tabs' => [
                            [
                                'tab_slug' => 'finalised',
                                'label' => $parent->orderHandlingStats->number_orders_state_finalised,
                                'type' => 'number',
                                'icon' => 'fal fa-tachometer-alt',
                                'information' => [
                                    'label' => $parent->orderHandlingStats->orders_state_finalised_amount_org_currency,
                                    'type' => 'currency'
                                ]
                            ]
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
                                'label' => $parent->orderHandlingStats->number_orders_dispatched_today,
                                'type'  => 'number',
                                'information' => [
                                    'label' => $parent->orderHandlingStats->orders_dispatched_today_amount_org_currency,
                                    'type' => 'currency'
                                ]
                            ],
                        ]
                    ]
                ],
                
                'creating' => $this->tab == 'creating' ?
                fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: 'creating'))
                : Inertia::lazy(fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: 'creating'))),
                
                'submitted' => $this->tab == 'submitted' ?
                fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: 'submitted'))
                : Inertia::lazy(fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: 'submitted'))),
                
                'in_warehouse' => $this->tab == 'in_warehouse' ?
                fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: 'in_warehouse'))
                : Inertia::lazy(fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: 'in_warehouse'))),
                
                'handling' => $this->tab == 'handling' ?
                fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: 'handling'))
                : Inertia::lazy(fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: 'handling'))),
                
                'handling_blocked' => $this->tab == 'handling_blocked' ?
                fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: 'handling_blocked'))
                : Inertia::lazy(fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: 'handling_blocked'))),
                
                'packed' => $this->tab == 'packed' ?
                fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: 'packed'))
                : Inertia::lazy(fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: 'packed'))),
                
                'finalised' => $this->tab == 'finalised' ?
                fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: 'finalised'))
                : Inertia::lazy(fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: 'finalised'))),
                
                'dispatched_today' => $this->tab == 'dispatched_today' ?
                fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: 'dispatched_today'))
                : Inertia::lazy(fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: 'dispatched_today'))),

            ]
        )->table(IndexOrders::make()->tableStructure(parent:$parent, prefix: 'creating'))
        ->table(IndexOrders::make()->tableStructure(parent:$parent, prefix: 'submitted'))
        ->table(IndexOrders::make()->tableStructure(parent:$parent, prefix: 'in_warehouse'))
        ->table(IndexOrders::make()->tableStructure(parent:$parent, prefix: 'handling'))
        ->table(IndexOrders::make()->tableStructure(parent:$parent, prefix: 'handling_blocked'))
        ->table(IndexOrders::make()->tableStructure(parent:$parent, prefix: 'packed'))
        ->table(IndexOrders::make()->tableStructure(parent:$parent, prefix: 'finalised'))
        ->table(IndexOrders::make()->tableStructure(parent:$parent, prefix: 'dispatched_today'));
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
