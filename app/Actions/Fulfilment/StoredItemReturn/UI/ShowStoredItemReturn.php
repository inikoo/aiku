<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 17:41:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItemReturn\UI;

use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasFulfilmentAssetsAuthorisation;
use App\Enums\Fulfilment\StoredItemReturn\StoredItemReturnStateEnum;
use App\Enums\UI\Fulfilment\StoredItemReturnTabsEnum;
use App\Http\Resources\Fulfilment\StoredItemResource;
use App\Http\Resources\Fulfilment\StoredItemReturnResource;
use App\Http\Resources\Fulfilment\StoredItemReturnsResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\StoredItemReturn;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowStoredItemReturn extends OrgAction
{
    use HasFulfilmentAssetsAuthorisation;
    private Warehouse|FulfilmentCustomer $parent;

    public function handle(StoredItemReturn $storedItemReturn): StoredItemReturn
    {
        return $storedItemReturn;
    }


    public function asController(Organisation $organisation, Warehouse $warehouse, StoredItemReturn $storedItemReturn, ActionRequest $request): StoredItemReturn
    {
        $this->parent = $warehouse;
        $this->initialisationFromWarehouse($warehouse, $request)->withTab(StoredItemReturnTabsEnum::values());

        return $this->handle($storedItemReturn);
    }

    public function inWarehouse(Organisation $organisation, Warehouse $warehouse, StoredItemReturn $storedItemReturn, ActionRequest $request): StoredItemReturn
    {
        $this->parent = $warehouse;
        $this->initialisationFromWarehouse($warehouse, $request)->withTab(StoredItemReturnTabsEnum::values());

        return $this->handle($storedItemReturn);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, StoredItemReturn $storedItemReturn, ActionRequest $request): StoredItemReturn
    {
        $this->parent = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(StoredItemReturnTabsEnum::values());

        return $this->handle($storedItemReturn);
    }

    public function htmlResponse(StoredItemReturn $storedItemReturn, ActionRequest $request): Response
    {
        if ($this->parent instanceof Warehouse) {
            $container = [
                'icon'    => ['fal', 'fa-warehouse'],
                'tooltip' => __('Warehouse'),
                'label'   => Str::possessive($this->parent->code)
            ];
        } else {
            $container = [
                'icon'    => ['fal', 'fa-user'],
                'tooltip' => __('Customer'),
                'label'   => Str::possessive($this->parent->customer->reference)
            ];
        }

        return Inertia::render(
            'Org/Fulfilment/StoredItemReturn',
            [
                'title'       => __('stored item return'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation' => [
                    'previous' => $this->getPrevious($storedItemReturn, $request),
                    'next'     => $this->getNext($storedItemReturn, $request),
                ],
                'pageHead' => [
                    'container' => $container,
                    'title'     => $storedItemReturn->reference,
                    'icon'      => [
                        'icon'  => ['fal', 'fa-truck-couch'],
                        'title' => $storedItemReturn->reference
                    ],
                    'edit' => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                            'parameters' => array_values($request->route()->originalParameters())
                        ]
                    ] : false,
                    'actions' => $storedItemReturn->state == StoredItemReturnStateEnum::IN_PROCESS ? [
                        [
                            'type'   => 'buttonGroup',
                            'key'    => 'upload-add',
                            'button' => [
                                [
                                    'type'  => 'button',
                                    'style' => 'tertiary',
                                    'icon'  => 'fal fa-plus',
                                    'label' => __('add stored item'),
                                    'route' => [
                                        'name'       => 'grp.models.fulfilment-customer.stored-item-return.stored-item.store',
                                        'parameters' => [
                                            'organisation'           => $storedItemReturn->organisation->id,
                                            'fulfilment'             => $storedItemReturn->fulfilment->id,
                                            'fulfilmentCustomer'     => $storedItemReturn->fulfilmentCustomer->id,
                                            'storedItemReturn'       => $storedItemReturn->id
                                        ]
                                    ]
                                ],
                            ]
                        ],
                        $storedItemReturn->items()->count() > 0 ? [
                            'type'    => 'button',
                            'style'   => 'save',
                            'tooltip' => __('Submit'),
                            'label'   => __('submit'),
                            'key'     => 'action',
                            'route'   => [
                                'method'     => 'post',
                                'name'       => 'grp.models.fulfilment-customer.stored-item-return.state.update',
                                'parameters' => [
                                    'organisation'           => $storedItemReturn->organisation->id,
                                    'fulfilment'             => $storedItemReturn->fulfilment->id,
                                    'fulfilmentCustomer'     => $storedItemReturn->fulfilmentCustomer->id,
                                    'storedItemReturn'       => $storedItemReturn->id,
                                    'state'                  => StoredItemReturnStateEnum::SUBMITTED->value
                                ]
                            ]
                        ] : [],
                    ] : [
                        $storedItemReturn->state == StoredItemReturnStateEnum::SUBMITTED ? [
                            'type'    => 'button',
                            'style'   => 'save',
                            'tooltip' => __('confirm'),
                            'label'   => __('confirm'),
                            'key'     => 'action',
                            'route'   => [
                                'method'     => 'post',
                                'name'       => 'grp.models.fulfilment-customer.stored-item-return.state.update',
                                'parameters' => [
                                    'organisation'           => $storedItemReturn->organisation->id,
                                    'fulfilment'             => $storedItemReturn->fulfilment->id,
                                    'fulfilmentCustomer'     => $storedItemReturn->fulfilmentCustomer->id,
                                    'storedItemReturn'       => $storedItemReturn->id,
                                    'state'                  => StoredItemReturnStateEnum::CONFIRMED->value
                                ]
                            ]
                        ] : [],
                        $storedItemReturn->state == StoredItemReturnStateEnum::CONFIRMED ? [
                            'type'    => 'button',
                            'style'   => 'save',
                            'tooltip' => __('picking'),
                            'label'   => __('picking'),
                            'key'     => 'action',
                            'route'   => [
                                'method'     => 'post',
                                'name'       => 'grp.models.fulfilment-customer.stored-item-return.state.update',
                                'parameters' => [
                                    'organisation'           => $storedItemReturn->organisation->id,
                                    'fulfilment'             => $storedItemReturn->fulfilment->id,
                                    'fulfilmentCustomer'     => $storedItemReturn->fulfilmentCustomer->id,
                                    'storedItemReturn'       => $storedItemReturn->id,
                                    'state'                  => StoredItemReturnStateEnum::PICKING->value
                                ]
                            ]
                        ] : [],
                        $storedItemReturn->state == StoredItemReturnStateEnum::PICKING ? [
                            'type'    => 'button',
                            'style'   => 'save',
                            'tooltip' => __('picked'),
                            'label'   => __('picked'),
                            'key'     => 'action',
                            'route'   => [
                                'method'     => 'post',
                                'name'       => 'grp.models.fulfilment-customer.stored-item-return.state.update',
                                'parameters' => [
                                    'organisation'           => $storedItemReturn->organisation->id,
                                    'fulfilment'             => $storedItemReturn->fulfilment->id,
                                    'fulfilmentCustomer'     => $storedItemReturn->fulfilmentCustomer->id,
                                    'storedItemReturn'       => $storedItemReturn->id,
                                    'state'                  => StoredItemReturnStateEnum::PICKED->value
                                ]
                            ]
                        ] : [],
                        $storedItemReturn->state == StoredItemReturnStateEnum::PICKED ? [
                            'type'    => 'button',
                            'style'   => 'save',
                            'tooltip' => __('dispatched'),
                            'label'   => __('dispatched'),
                            'key'     => 'action',
                            'route'   => [
                                'method'     => 'post',
                                'name'       => 'grp.models.fulfilment-customer.stored-item-return.state.update',
                                'parameters' => [
                                    'organisation'           => $storedItemReturn->organisation->id,
                                    'fulfilment'             => $storedItemReturn->fulfilment->id,
                                    'fulfilmentCustomer'     => $storedItemReturn->fulfilmentCustomer->id,
                                    'storedItemReturn'       => $storedItemReturn->id,
                                    'state'                  => StoredItemReturnStateEnum::DISPATCHED->value
                                ]
                            ]
                        ] : [],
                    ],
                ],

                'updateRoute' => [
                    'route' => [
                        'name'       => 'grp.models.fulfilment-customer.pallet-return.timeline.update',
                        'parameters' => [
                            'organisation'           => $storedItemReturn->organisation->id,
                            'fulfilment'             => $storedItemReturn->fulfilment->id,
                            'fulfilmentCustomer'     => $storedItemReturn->fulfilmentCustomer->id,
                            'storedItemReturn'       => $storedItemReturn->id
                        ]
                    ]
                ],

                'upload' => [
                    'event'   => 'action-progress',
                    'channel' => 'grp.personal.' . $this->organisation->id
                ],

                'uploadRoutes' => [
                    'history' => [
                        'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet-returns.pallets.uploads.history',
                        'parameters' => [
                            'organisation'           => $storedItemReturn->organisation->slug,
                            'fulfilment'             => $storedItemReturn->fulfilment->slug,
                            'fulfilmentCustomer'     => $storedItemReturn->fulfilmentCustomer->id,
                            'storedItemReturn'       => $storedItemReturn->reference
                        ]
                    ],
                    'download' => [
                        'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet-returns.pallets.uploads.templates',
                        'parameters' => [
                            'organisation'           => $storedItemReturn->organisation->slug,
                            'fulfilment'             => $storedItemReturn->fulfilment->slug,
                            'fulfilmentCustomer'     => $storedItemReturn->fulfilmentCustomer->slug,
                            'storedItemReturn'       => $storedItemReturn->reference
                        ]
                    ],
                ],

                'storedItemRoute' => [
                    'index' => [
                        'name'       => 'grp.org.fulfilments.show.crm.customers.show.stored-item-returns.stored-items.booked-in.index',
                        'parameters' => [
                            'organisation'       => $storedItemReturn->organisation->slug,
                            'fulfilment'         => $storedItemReturn->fulfilment->slug,
                            'fulfilmentCustomer' => $storedItemReturn->fulfilmentCustomer->slug
                        ]
                    ],
                    'store' => [
                        'name'       => 'grp.models.fulfilment-customer.stored-item-return.stored-item.store',
                        'parameters' => [
                            'organisation'       => $storedItemReturn->organisation->id,
                            'fulfilment'         => $storedItemReturn->fulfilment->id,
                            'fulfilmentCustomer' => $storedItemReturn->fulfilmentCustomer->id,
                            'storedItemReturn'   => $storedItemReturn->id
                        ]
                    ]
                ],

                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => StoredItemReturnTabsEnum::navigation()
                ],

                'data' => StoredItemReturnResource::make($storedItemReturn),

                StoredItemReturnTabsEnum::ITEMS->value => $this->tab == StoredItemReturnTabsEnum::ITEMS->value ?
                    fn () => StoredItemResource::collection(IndexStoredItemReturnStoredItems::run($storedItemReturn, 'stored_items'))
                    : Inertia::lazy(fn () => StoredItemResource::collection(IndexStoredItemReturnStoredItems::run($storedItemReturn, 'stored_items'))),
            ]
        )->table(
            IndexStoredItemReturnStoredItems::make()->tableStructure(
                $storedItemReturn,
                prefix: 'stored_items'
            )
        );
    }


    public function jsonResponse(StoredItemReturn $storedItemReturn): StoredItemReturnsResource
    {
        return new StoredItemReturnsResource($storedItemReturn);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, $suffix = ''): array
    {
        $headCrumb = function (StoredItemReturn $storedItemReturn, array $routeParameters, string $suffix) {
            return [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('stored item returns')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $storedItemReturn->reference,
                        ],

                    ],
                    'suffix' => $suffix
                ],
            ];
        };

        $storedItemReturn = StoredItemReturn::where('slug', $routeParameters['storedItemReturn'])->first();

        return match ($routeName) {
            'grp.org.fulfilments.show.crm.customers.show.stored-item-returns.show' => array_merge(
                ShowFulfilmentCustomer::make()->getBreadcrumbs(Arr::only($routeParameters, ['organisation', 'fulfilment', 'fulfilmentCustomer'])),
                $headCrumb(
                    $storedItemReturn,
                    [
                        'index' => [
                            'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet-returns.index',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment', 'fulfilmentCustomer'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.fulfilments.show.crm.customers.show.stored-item-returns.show',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment', 'fulfilmentCustomer', 'storedItemReturn'])
                        ]
                    ],
                    $suffix
                )
            ),
            'grp.org.warehouses.show.fulfilment.pallet-returns.show' => array_merge(
                ShowWarehouse::make()->getBreadcrumbs(
                    Arr::only($routeParameters, ['organisation', 'warehouse'])
                ),
                $headCrumb(
                    $storedItemReturn,
                    [
                        'index' => [
                            'name'       => 'grp.org.warehouses.show.fulfilment.pallet-returns.index',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'warehouse'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.warehouses.show.fulfilment.pallet-returns.show',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'warehouse', 'storedItemReturn'])
                        ]
                    ],
                    $suffix
                ),
            ),

            default => []
        };
    }

    public function getPrevious(StoredItemReturn $storedItemReturn, ActionRequest $request): ?array
    {
        $previous = StoredItemReturn::where('id', '<', $storedItemReturn->id)->orderBy('id', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(StoredItemReturn $storedItemReturn, ActionRequest $request): ?array
    {
        $next = StoredItemReturn::where('id', '>', $storedItemReturn->id)->orderBy('id')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?StoredItemReturn $storedItemReturn, string $routeName): ?array
    {
        if (!$storedItemReturn) {
            return null;
        }


        return match (class_basename($this->parent)) {
            'Warehouse' => [
                'label' => $storedItemReturn->reference,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation'       => $storedItemReturn->organisation->slug,
                        'warehouse'          => $storedItemReturn->warehouse->slug,
                        'storedItemReturn'   => $storedItemReturn->reference
                    ]

                ]
            ],
            'FulfilmentCustomer' => [
                'label' => $storedItemReturn->reference,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation'           => $storedItemReturn->organisation->slug,
                        'fulfilment'             => $storedItemReturn->fulfilment->slug,
                        'fulfilmentCustomer'     => $storedItemReturn->fulfilmentCustomer->slug,
                        'storedItemReturn'       => $storedItemReturn->reference
                    ]

                ]
            ],
            default => []
        };
    }
}
