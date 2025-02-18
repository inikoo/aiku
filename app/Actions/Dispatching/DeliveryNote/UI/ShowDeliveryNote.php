<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 13:48:15 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\DeliveryNote\UI;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\CRM\Customer\UI\ShowCustomer;
use App\Actions\Dispatching\DeliveryNoteItem\UI\IndexDeliveryNoteItems;
use App\Actions\Dispatching\Picking\UI\IndexPickings;
use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\Ordering\Order\UI\ShowOrder;
use App\Actions\OrgAction;
use App\Actions\UI\WithInertia;
use App\Enums\Dispatching\DeliveryNote\DeliveryNoteStateEnum;
use App\Enums\UI\Dispatch\DeliveryNoteTabsEnum;
use App\Http\Resources\CRM\CustomerResource;
use App\Http\Resources\Dispatching\DeliveryNoteItemsResource;
use App\Http\Resources\Dispatching\DeliveryNoteResource;
use App\Http\Resources\Dispatching\PickingsResource;
use App\Http\Resources\Helpers\AddressResource;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Customer;
use App\Models\Dispatching\DeliveryNote;
use App\Models\Helpers\Address;
use App\Models\Inventory\Warehouse;
use App\Models\Ordering\Order;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowDeliveryNote extends OrgAction
{
    use AsAction;
    use WithInertia;

    private Order|Shop|Warehouse|Customer $parent;

    public function handle(DeliveryNote $deliveryNote): DeliveryNote
    {
        return $deliveryNote;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->parent instanceof Order) {
            $this->canEdit      = $request->user()->authTo("orders.{$this->shop->id}.edit");
            return $request->user()->authTo("orders.{$this->shop->id}.view");
        } elseif ($this->parent instanceof Customer) {
            $this->canEdit      = $request->user()->authTo("orders.{$this->shop->id}.edit");
            return $request->user()->authTo("orders.{$this->shop->id}.view");
        }
        $this->canEdit      = $request->user()->authTo("dispatching.{$this->warehouse->id}.edit");
        return $request->user()->authTo("dispatching.{$this->warehouse->id}.view");
    }

    public function inOrganisation(DeliveryNote $deliveryNote): DeliveryNote
    {
        return $this->handle($deliveryNote);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Shop $shop, DeliveryNote $deliveryNote, ActionRequest $request): DeliveryNote
    {
        return $this->handle($deliveryNote);
    }

    public function inWarehouse(Organisation $organisation, Warehouse $warehouse, DeliveryNote $deliveryNote, ActionRequest $request): DeliveryNote
    {
        $this->parent = $warehouse;
        $this->initialisationFromWarehouse($warehouse, $request)->withTab(DeliveryNoteTabsEnum::values());

        return $this->handle($deliveryNote);
    }
    /** @noinspection PhpUnusedParameterInspection */
    public function inOrder(Organisation $organisation, Shop $shop, Order $order, DeliveryNote $deliveryNote, ActionRequest $request): DeliveryNote
    {
        return $this->handle($deliveryNote);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inOrderInShop(Organisation $organisation, Shop $shop, Order $order, DeliveryNote $deliveryNote, ActionRequest $request): DeliveryNote
    {
        $this->parent = $order;
        $this->initialisationFromShop($shop, $request)->withTab(DeliveryNoteTabsEnum::values());
        return $this->handle($deliveryNote);
    }

    public function inCustomerInShop(Organisation $organisation, Shop $shop, Customer $customer, DeliveryNote $deliveryNote, ActionRequest $request): DeliveryNote
    {
        $this->parent = $customer;
        $this->initialisationFromShop($shop, $request)->withTab(DeliveryNoteTabsEnum::values());
        return $this->handle($deliveryNote);
    }

    public function htmlResponse(DeliveryNote $deliveryNote, ActionRequest $request): Response
    {

        $timeline       = [];
        foreach (DeliveryNoteStateEnum::cases() as $state) {

            $timestamp = $deliveryNote->{$state->snake() . '_at'}
            ? $deliveryNote->{$state->snake() . '_at'}
            : null;

            $timestamp = $timestamp ?: null;

            $timeline[$state->value] = [
                'label'     => $state->labels()[$state->value],
                'tooltip'   => $state->labels()[$state->value],
                'key'       => $state->value,
                'timestamp' => $timestamp
            ];
        }

        $finalTimeline = $timeline;

        $estWeight = ($deliveryNote->estimated_weight ?? 0) / 1000;

        $actions = [];
        if ($this->canEdit) {
            $actions = match ($deliveryNote->state) {
                DeliveryNoteStateEnum::UNASSIGNED => [
                    [
                        'type'    => 'button',
                        'style'   => 'save',
                        'tooltip' => __('In Queue'),
                        'label'   => __('In Queue'),
                        'key'     => 'action',
                        'route'   => [
                            'method'     => 'patch',
                            'name'       => 'grp.models.delivery-note.state.in-queue',
                            'parameters' => [
                                'deliveryNote' => $deliveryNote->id
                            ]
                        ]
                    ]
                ],
                DeliveryNoteStateEnum::QUEUED => [
                    [
                        'type'    => 'button',
                        'style'   => 'save',
                        'tooltip' => __('In Queue'),
                        'label'   => __('In Queue'),
                        'key'     => 'action',
                        'route'   => [
                            'method'     => 'patch',
                            'name'       => 'grp.models.delivery-note.state.picker-assigned',
                            'parameters' => [
                                'deliveryNote' => $deliveryNote->id
                            ]
                        ]
                    ]
                ],

                DeliveryNoteStateEnum::HANDLING => [
                    [
                        'type'    => 'button',
                        'style'   => 'save',
                        'tooltip' => __('Handling'),
                        'label'   => __('Handling'),
                        'key'     => 'action-picked',
                        'route'   => [
                            'method'     => 'patch',
                            'name'       => 'grp.models.delivery-note.state.picked',
                            'parameters' => [
                                'deliveryNote' => $deliveryNote->id
                            ]
                        ]
                    ]
                ],


                DeliveryNoteStateEnum::PACKED => [
                    [
                        'type'    => 'button',
                        'style'   => 'save',
                        'tooltip' => __('Finalised'),
                        'label'   => __('Finalised'),
                        'key'     => 'action',
                        'route'   => [
                            'method'     => 'patch',
                            'name'       => 'grp.models.delivery-note.state.finalised',
                            'parameters' => [
                                'deliveryNote' => $deliveryNote->id
                            ]
                        ]
                    ]
                ],
                DeliveryNoteStateEnum::FINALISED => [
                    [
                        'type'    => 'button',
                        'style'   => 'save',
                        'tooltip' => __('Settled'),
                        'label'   => __('Settled'),
                        'key'     => 'action',
                        'route'   => [
                            'method'     => 'patch',
                            'name'       => 'grp.models.delivery-note.state.settled',
                            'parameters' => [
                                'deliveryNote' => $deliveryNote->id
                            ]
                        ]
                    ]
                ],
                default => []
            };
        }

        return Inertia::render(
            'Org/Dispatching/DeliveryNote',
            [
                'title'                                 => __('delivery note'),
                'breadcrumbs'                           => $this->getBreadcrumbs(
                    $deliveryNote,
                    $request->route()->getName(),
                    $request->route()->originalParameters(),
                ),
                'navigation'                            => [
                    'previous' => $this->getPrevious($deliveryNote, $request),
                    'next'     => $this->getNext($deliveryNote, $request),
                ],
                'pageHead'      => [
                    'title'     => $deliveryNote->reference,
                    'model'     => __('Delivery Note'),
                    'icon'      => [
                        'icon'  => 'fal fa-truck',
                        'title' => __('delivery note')
                    ],
                    'actions' => $actions
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => DeliveryNoteTabsEnum::navigation($deliveryNote)
                ],
                'delivery_note' => DeliveryNoteResource::make($deliveryNote)->toArray(request()),


                'alert'     => 'zzzzzzzzzz',
                'notes'     => 'zzzzzzzzz',
                'timelines' => $finalTimeline,
                'box_stats' => [
                    'state'  => $deliveryNote->state,
                    'customer'          => array_merge(
                        CustomerResource::make($deliveryNote->customer)->getArray(),
                        [
                            'addresses'      => [
                                'delivery'   => AddressResource::make($deliveryNote->deliveryAddress ?? new Address()),
                            ],
                        ]
                    ),
                    'products'  => [
                        'estimated_weight' => $estWeight,
                        'number_items'     => $deliveryNote->stats->number_items,
                    ],
                    'picker' => $deliveryNote->picker,
                    'packer' => $deliveryNote->packer,
                    // 'warehouse' => [
                    //     'picker' => $deliveryNote->picker->alias ?? null,
                    //     'packer' => $deliveryNote->packer->alias ?? null
                    // ]
                ],
                'routes'    => [
                    'update'    => [
                        'name'          => 'grp.models.delivery-note.update',
                        'parameters'    => [
                            'deliveryNote'  => $deliveryNote->id
                        ]
                    ],
                    'products_list'    => [
                        'name'          => 'xxxxxxxxxxxxx',
                        'parameters'    => 'xxxxxxx'
                    ],
                    'pickers_list'  => [
                        'name'          => 'grp.json.employees.pickers',
                        'parameters'    => [
                            'organisation' => $deliveryNote->organisation->slug
                        ]
                        ],
                    'packers_list'  => [
                        'name'          => 'grp.json.employees.packers',
                        'parameters'    => [
                            'organisation' => $deliveryNote->organisation->slug
                        ]
                    ],
                    'exportPdfRoute' => [
                        'name'       => 'grp.org.accounting.invoices.download',
                        'parameters' => [
                            'organisation' => $deliveryNote->organisation->slug,
                            'invoice'      => $deliveryNote->slug
                        ]
                    ],
                ],

                DeliveryNoteTabsEnum::ITEMS->value => $this->tab == DeliveryNoteTabsEnum::ITEMS->value ?
                fn () => DeliveryNoteItemsResource::collection(IndexDeliveryNoteItems::run($deliveryNote))
                : Inertia::lazy(fn () => DeliveryNoteItemsResource::collection(IndexDeliveryNoteItems::run($deliveryNote))),

                DeliveryNoteTabsEnum::PICKINGS->value => $this->tab == DeliveryNoteTabsEnum::PICKINGS->value ?
                fn () => PickingsResource::collection(IndexPickings::run($deliveryNote))
                : Inertia::lazy(fn () => PickingsResource::collection(IndexPickings::run($deliveryNote))),
            ]
        )
        ->table(IndexDeliveryNoteItems::make()->tableStructure(parent: $deliveryNote, prefix: DeliveryNoteTabsEnum::ITEMS->value))
        ->table(IndexPickings::make()->tableStructure(parent: $deliveryNote, prefix: DeliveryNoteTabsEnum::PICKINGS->value));
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->fillFromRequest($request);

        $this->set('canEdit', $request->user()->authTo('hr.edit'));
        $this->set('canViewUsers', $request->user()->authTo('users.view'));
    }

    #[Pure] public function jsonResponse(DeliveryNote $deliveryNote): DeliveryNoteResource
    {
        return new DeliveryNoteResource($deliveryNote);
    }


    public function getBreadcrumbs(DeliveryNote $deliveryNote, string $routeName, array $routeParameters, string $suffix = ''): array
    {
        $headCrumb = function (DeliveryNote $deliveryNote, array $routeParameters, string $suffix) {
            return [
                [

                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('Delivery Note')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $deliveryNote->reference,
                        ],

                    ],
                    'suffix'         => $suffix

                ],
            ];
        };

        return match ($routeName) {
            'grp.org.warehouses.show.dispatching.delivery-notes.show',
            => array_merge(
                ShowWarehouse::make()->getBreadcrumbs(
                    Arr::only($routeParameters, ['organisation', 'warehouse'])
                ),
                $headCrumb(
                    $deliveryNote,
                    [
                        'index' => [
                            'name'       => 'grp.org.warehouses.show.dispatching.delivery-notes',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'warehouse'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.warehouses.show.dispatching.delivery-notes.show',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'warehouse', 'deliveryNote'])
                        ]
                    ],
                    $suffix
                ),
            ),
            'grp.org.shops.show.ordering.orders.show.delivery-note',
            => array_merge(
                ShowOrder::make()->getBreadcrumbs(
                    $this->parent,
                    $routeName,
                    $routeParameters
                ),
                $headCrumb(
                    $deliveryNote,
                    [
                        'index' => [
                            'name'       => 'grp.org.shops.show.ordering.orders.show',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'shop', 'order'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.shops.show.ordering.orders.show.delivery-note',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'shop', 'order', 'deliveryNote'])
                        ]
                    ],
                    $suffix
                ),
            ),
            'grp.org.shops.show.ordering.show.delivery-note.show',
            => array_merge(
                ShowShop::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    $deliveryNote,
                    [
                        'index' => [
                            'name'       => 'grp.org.shops.show.ordering.orders.index',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'shop'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.shops.show.ordering.show.delivery-note.show',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'shop', 'deliveryNote'])
                        ]
                    ],
                    $suffix
                ),
            ),
            'grp.org.shops.show.crm.customers.show.delivery_notes.show',
            => array_merge(
                ShowCustomer::make()->getBreadcrumbs(
                    'grp.org.shops.show.crm.customers.show',
                    $routeParameters
                ),
                $headCrumb(
                    $deliveryNote,
                    [
                        'index' => [
                            'name'       => 'grp.org.shops.show.crm.customers.show.delivery_notes.index',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'shop', 'customer'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.shops.show.crm.customers.show.delivery_notes.show',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'shop', 'customer', 'deliveryNote'])
                        ]
                    ],
                    $suffix
                ),
            ),
            default => []
        };
    }


    public function getPrevious(DeliveryNote $deliveryNote, ActionRequest $request): ?array
    {

        $previous = DeliveryNote::where('reference', '<', $deliveryNote->reference)->when(true, function ($query) use ($deliveryNote, $request) {
            if ($request->route()->getName() == 'shops.show.delivery-notes.show') {
                $query->where('delivery_notes.shop_id', $deliveryNote->shop_id);
            } elseif ($request->route()->getName() == 'grp.org.shops.show.ordering.orders.show.delivery-note') {
                $query->leftjoin('delivery_note_order', 'delivery_note_order.delivery_note_id', '=', 'delivery_notes.id');
                $query->where('delivery_note_order.order_id', $this->parent->id);
            } elseif ($request->route()->getName() == 'grp.org.shops.show.crm.customers.show.delivery_notes.show') {
                $query->where('delivery_notes.customer_id', $this->parent->id);
            }
        })->orderBy('reference', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName(), $request->route()->originalParameters());

    }

    public function getNext(DeliveryNote $deliveryNote, ActionRequest $request): ?array
    {
        $next = DeliveryNote::where('reference', '>', $deliveryNote->reference)->when(true, function ($query) use ($deliveryNote, $request) {
            if ($request->route()->getName() == 'shops.show.delivery-notes.show') {
                $query->where('delivery_notes.shop_id', $deliveryNote->shop_id);
            } elseif ($request->route()->getName() == 'grp.org.shops.show.ordering.orders.show.delivery-note') {
                $query->leftjoin('delivery_note_order', 'delivery_note_order.delivery_note_id', '=', 'delivery_notes.id');
                $query->where('delivery_note_order.order_id', $this->parent->id);
            } elseif ($request->route()->getName() == 'grp.org.shops.show.crm.customers.show.delivery_notes.show') {
                $query->where('delivery_notes.customer_id', $this->parent->id);
            }
        })->orderBy('reference')->first();

        return $this->getNavigation($next, $request->route()->getName(), $request->route()->originalParameters());
    }

    private function getNavigation(?DeliveryNote $deliveryNote, string $routeName, $routeParameters): ?array
    {
        if (!$deliveryNote) {
            return null;
        }

        return match ($routeName) {
            'delivery-notes.show' ,
            'shops.delivery-notes.show' => [
                'label' => $deliveryNote->reference,
                'route' => [
                    'name'      => $routeName,
                    'parameters' => [
                        'deliveryNote' => $deliveryNote->slug
                    ]

                ]
            ],
            'shops.show.delivery-notes.show' => [
                'label' => $deliveryNote->reference,
                'route' => [
                    'name'      => $routeName,
                    'parameters' => [
                        'shop'        => $deliveryNote->shop->slug,
                        'deliveryNote' => $deliveryNote->slug
                    ]

                ]
            ],
            'grp.org.warehouses.show.dispatching.delivery-notes.show' => [
                'label' => $deliveryNote->reference,
                'route' => [
                    'name'      => $routeName,
                    'parameters' => [
                        'organisation'  => $deliveryNote->organisation->slug,
                        'warehouse'     => $deliveryNote->warehouse->slug,
                        'deliveryNote'  => $deliveryNote->slug
                    ]

                ]
            ],
            'grp.org.shops.show.ordering.show.delivery-note.show' => [
                'label' => $deliveryNote->reference,
                'route' => [
                    'name'      => $routeName,
                    'parameters' => Arr::only($routeParameters, ['organisation', 'shop', 'deliveryNote'])

                ]
            ],
            'grp.org.shops.show.ordering.orders.show.delivery-note' => [
                'label' => $deliveryNote->reference,
                'route' => [
                    'name'      => $routeName,
                    'parameters' => Arr::only($routeParameters, ['organisation', 'shop', 'order', 'deliveryNote'])

                ]
            ],
            'grp.org.shops.show.crm.customers.show.delivery_notes.show' => [
                'label' => $deliveryNote->reference,
                'route' => [
                    'name'      => $routeName,
                    'parameters' => Arr::only($routeParameters, ['organisation', 'shop', 'customer', 'deliveryNote'])

                ]
            ]
        };
    }
}
