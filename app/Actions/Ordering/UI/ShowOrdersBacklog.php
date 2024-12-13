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
use App\Enums\UI\Ordering\OrdersBacklogTabsEnum;
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

        $tabsBox = [
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
        ];
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

                
                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => $tabsBox
                ],

                
                OrdersBacklogTabsEnum::CREATING->value => $this->tab == OrdersBacklogTabsEnum::CREATING->value ?
                fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: OrdersBacklogTabsEnum::CREATING->value))
                : Inertia::lazy(fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: OrdersBacklogTabsEnum::CREATING->value))),
                
                OrdersBacklogTabsEnum::SUBMITTED->value => $this->tab == OrdersBacklogTabsEnum::SUBMITTED->value ?
                fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: OrdersBacklogTabsEnum::SUBMITTED->value))
                : Inertia::lazy(fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: OrdersBacklogTabsEnum::SUBMITTED->value))),
                
                OrdersBacklogTabsEnum::IN_WAREHOUSE->value => $this->tab == OrdersBacklogTabsEnum::IN_WAREHOUSE->value ?
                fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: OrdersBacklogTabsEnum::IN_WAREHOUSE->value))
                : Inertia::lazy(fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: OrdersBacklogTabsEnum::IN_WAREHOUSE->value))),
                
                OrdersBacklogTabsEnum::HANDLING->value => $this->tab == OrdersBacklogTabsEnum::HANDLING->value ?
                fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: OrdersBacklogTabsEnum::HANDLING->value))
                : Inertia::lazy(fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: OrdersBacklogTabsEnum::HANDLING->value))),
                
                OrdersBacklogTabsEnum::HANDLING_BLOCKED->value => $this->tab == OrdersBacklogTabsEnum::HANDLING_BLOCKED->value ?
                fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: OrdersBacklogTabsEnum::HANDLING_BLOCKED->value))
                : Inertia::lazy(fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: OrdersBacklogTabsEnum::HANDLING_BLOCKED->value))),
                
                OrdersBacklogTabsEnum::PACKED->value => $this->tab == OrdersBacklogTabsEnum::PACKED->value ?
                fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: OrdersBacklogTabsEnum::PACKED->value))
                : Inertia::lazy(fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: OrdersBacklogTabsEnum::PACKED->value))),
                
                OrdersBacklogTabsEnum::FINALISED->value => $this->tab == OrdersBacklogTabsEnum::FINALISED->value ?
                fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: OrdersBacklogTabsEnum::FINALISED->value))
                : Inertia::lazy(fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: OrdersBacklogTabsEnum::FINALISED->value))),
                
                OrdersBacklogTabsEnum::DISPATCHED_TODAY->value => $this->tab == OrdersBacklogTabsEnum::DISPATCHED_TODAY->value ?
                fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: OrdersBacklogTabsEnum::DISPATCHED_TODAY->value))
                : Inertia::lazy(fn () => OrdersResource::collection(IndexOrders::run(parent: $parent, bucket: OrdersBacklogTabsEnum::DISPATCHED_TODAY->value))),

            ]
        )->table(IndexOrders::make()->tableStructure(parent:$parent, prefix: OrdersBacklogTabsEnum::CREATING->value))
        ->table(IndexOrders::make()->tableStructure(parent:$parent, prefix: OrdersBacklogTabsEnum::SUBMITTED->value))
        ->table(IndexOrders::make()->tableStructure(parent:$parent, prefix: OrdersBacklogTabsEnum::IN_WAREHOUSE->value))
        ->table(IndexOrders::make()->tableStructure(parent:$parent, prefix: OrdersBacklogTabsEnum::HANDLING->value))
        ->table(IndexOrders::make()->tableStructure(parent:$parent, prefix: OrdersBacklogTabsEnum::HANDLING_BLOCKED->value))
        ->table(IndexOrders::make()->tableStructure(parent:$parent, prefix: OrdersBacklogTabsEnum::PACKED->value))
        ->table(IndexOrders::make()->tableStructure(parent:$parent, prefix: OrdersBacklogTabsEnum::FINALISED->value))
        ->table(IndexOrders::make()->tableStructure(parent:$parent, prefix: OrdersBacklogTabsEnum::DISPATCHED_TODAY->value));
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
