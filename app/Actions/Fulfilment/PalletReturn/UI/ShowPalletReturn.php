<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 17:41:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn\UI;

use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\Pallet\UI\IndexPalletsInReturn;
use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasFulfilmentAssetsAuthorisation;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Enums\UI\Fulfilment\PalletReturnTabsEnum;
use App\Http\Resources\Fulfilment\FulfilmentCustomerResource;
use App\Http\Resources\Fulfilment\PalletReturnItemsResource;
use App\Http\Resources\Fulfilment\PalletReturnResource;
use App\Http\Resources\Fulfilment\PalletReturnsResource;
use App\Http\Resources\Fulfilment\PhysicalGoodsResource;
use App\Http\Resources\Fulfilment\ServicesResource;
use App\Http\Resources\Helpers\AddressResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletReturn;
use App\Actions\Helpers\Country\UI\GetAddressData;
use App\Models\Helpers\Address;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowPalletReturn extends OrgAction
{
    use HasFulfilmentAssetsAuthorisation;
    private Warehouse|FulfilmentCustomer|Fulfilment $parent;

    public function handle(PalletReturn $palletReturn): PalletReturn
    {
        return $palletReturn;
    }


    public function asController(Organisation $organisation, Fulfilment $fulfilment, PalletReturn $palletReturn, ActionRequest $request): PalletReturn
    {
        $this->parent = $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(PalletReturnTabsEnum::values());

        return $this->handle($palletReturn);
    }

    public function inWarehouse(Organisation $organisation, Warehouse $warehouse, PalletReturn $palletReturn, ActionRequest $request): PalletReturn
    {
        $this->parent = $warehouse;
        $this->initialisationFromWarehouse($warehouse, $request)->withTab(PalletReturnTabsEnum::values());

        return $this->handle($palletReturn);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, PalletReturn $palletReturn, ActionRequest $request): PalletReturn
    {
        $this->parent = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(PalletReturnTabsEnum::values());

        return $this->handle($palletReturn);
    }

    public function htmlResponse(PalletReturn $palletReturn, ActionRequest $request): Response
    {


        $actions = [];

        if($this->canEdit) {
            $actions = $palletReturn->state == PalletReturnStateEnum::IN_PROCESS ? [
                [
                    'type'   => 'buttonGroup',
                    'key'    => 'upload-add',
                    'button' => [
                        [
                            'type'  => 'button',
                            'style' => 'tertiary',
                            'icon'  => 'fal fa-plus',
                            'label' => __('add pallet'),
                            'route' => [
                                'name'       => 'grp.models.fulfilment-customer.pallet-return.pallet.store',
                                'parameters' => [
                                    'organisation'       => $palletReturn->organisation->id,
                                    'fulfilment'         => $palletReturn->fulfilment->id,
                                    'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->id,
                                    'palletReturn'       => $palletReturn->id
                                ]
                            ]
                        ],
                        [
                            'type'    => 'button',
                            'style'   => 'secondary',
                            'icon'    => 'fal fa-plus',
                            'label'   => __('add service'),
                            'tooltip' => __('Add single service'),
                            'route'   => [
                                'name'       => 'grp.models.pallet-return.service.store',
                                'parameters' => [
                                    'palletReturn' => $palletReturn->id
                                ]
                            ]
                        ],
                        [
                            'type'    => 'button',
                            'style'   => 'secondary',
                            'icon'    => 'fal fa-plus',
                            'label'   => __('add physical good'),
                            'tooltip' => __('Add physical good'),
                            'route'   => [
                                'name'       => 'grp.models.pallet-return.physical_good.store',
                                'parameters' => [
                                    'palletReturn' => $palletReturn->id
                                ]
                            ]
                        ],
                    ]
                ],
                $palletReturn->pallets()->count() > 0 ? [
                    'type'    => 'button',
                    'style'   => 'save',
                    'tooltip' => __('submit'),
                    'label'   => __('submit'),
                    'key'     => 'action',
                    'route'   => [
                        'method'     => 'post',
                        'name'       => 'grp.models.fulfilment-customer.pallet-return.submit',
                        'parameters' => [
                            'organisation'       => $palletReturn->organisation->slug,
                            'fulfilment'         => $palletReturn->fulfilment->slug,
                            'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->id,
                            'palletReturn'       => $palletReturn->id
                        ]
                    ]
                ] : [],
            ] : [
                $palletReturn->state == PalletReturnStateEnum::SUBMITTED ? [
                    'type'    => 'button',
                    'style'   => 'save',
                    'tooltip' => __('confirm'),
                    'label'   => __('confirm'),
                    'key'     => 'action',
                    'route'   => [
                        'method'     => 'post',
                        'name'       => 'grp.models.fulfilment-customer.pallet-return.confirm',
                        'parameters' => [
                            'organisation'       => $palletReturn->organisation->slug,
                            'fulfilment'         => $palletReturn->fulfilment->slug,
                            'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->id,
                            'palletReturn'       => $palletReturn->id
                        ]
                    ]
                ] : [],
                $palletReturn->state == PalletReturnStateEnum::CONFIRMED ? [
                    'type'    => 'button',
                    'style'   => 'save',
                    'tooltip' => __('start picking'),
                    'label'   => __('start picking'),
                    'key'     => 'action',
                    'route'   => [
                        'method'     => 'post',
                        'name'       => 'grp.models.fulfilment-customer.pallet-return.picking',
                        'parameters' => [
                            'organisation'       => $palletReturn->organisation->slug,
                            'fulfilment'         => $palletReturn->fulfilment->slug,
                            'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->id,
                            'palletReturn'       => $palletReturn->id
                        ]
                    ]
                ] : [],
                $palletReturn->state == PalletReturnStateEnum::PICKING ? [
                    'type'    => 'button',
                    'style'   => 'save',
                    'tooltip' => __('set all pending as picked'),
                    'label'   => __('pick all'),
                    'key'     => 'action',
                    'route'   => [
                        'method'     => 'post',
                        'name'       => 'grp.models.fulfilment-customer.pallet-return.picked',
                        'parameters' => [
                            'organisation'       => $palletReturn->organisation->slug,
                            'fulfilment'         => $palletReturn->fulfilment->slug,
                            'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->id,
                            'palletReturn'       => $palletReturn->id
                        ]
                    ]
                ] : [],
                $palletReturn->state == PalletReturnStateEnum::PICKED ? [
                    'type'    => 'button',
                    'style'   => 'save',
                    'tooltip' => __('set as dispatched'),
                    'label'   => __('Dispatching'),
                    'key'     => 'action',
                    'route'   => [
                        'method'     => 'post',
                        'name'       => 'grp.models.fulfilment-customer.pallet-return.dispatched',
                        'parameters' => [
                            'organisation'       => $palletReturn->organisation->slug,
                            'fulfilment'         => $palletReturn->fulfilment->slug,
                            'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->id,
                            'palletReturn'       => $palletReturn->id
                        ]
                    ]
                ] : [],
            ];

            if(!in_array($palletReturn->state, [
                PalletReturnStateEnum::IN_PROCESS,
                PalletReturnStateEnum::SUBMITTED
            ])) {
                $actions[] = [
                    'type'          => 'button',
                    'style'         => 'tertiary',
                    'icon'          => 'fal fa-file-export',
                    'id'            => 'pdf-export',
                    'label'         => 'PDF',
                    'key'           => 'action',
                    'target'        => '_blank',
                    'route'         => [
                        'name'       => 'grp.models.pallet-return.pdf',
                        'parameters' => [
                            'palletReturn'       => $palletReturn->id
                        ]
                    ]
                ];
            }
        }

        $addressHistories = AddressResource::collection($palletReturn->addresses()->where('scope', 'delivery')->get());

        return Inertia::render(
            'Org/Fulfilment/PalletReturn',
            [
                'title'       => __('pallet return'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation' => [
                    'previous' => $this->getPrevious($palletReturn, $request),
                    'next'     => $this->getNext($palletReturn, $request),
                ],
                'pageHead' => [
                    // 'container' => $container,
                    'title'     => $palletReturn->reference,
                    'model'     => __('pallet return'),
                    'icon'      => [
                        'icon'  => ['fal', 'fa-truck-couch'],
                        'title' => $palletReturn->reference
                    ],
                    'edit' => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                            'parameters' => array_values($request->route()->originalParameters())
                        ]
                    ] : false,
                    'actions' => $actions
                ],

                'updateRoute' => [
                    'name'       => 'grp.models.pallet-return.update',
                    'parameters' => [
                        'palletReturn'       => $palletReturn->id
                    ]
                ],

                'deleteServiceRoute' => [
                    'name'       => 'org.models.pallet-return.service.delete',
                    'parameters' => [
                        'palletReturn' => $palletReturn->id
                    ]
                ],

                'deletePhysicalGoodRoute' => [
                    'name'       => 'org.models.pallet-return.physical_good.delete',
                    'parameters' => [
                        'palletReturn' => $palletReturn->id
                    ]
                ],

                'upload' => [
                    'event'   => 'action-progress',
                    'channel' => 'grp.personal.' . $this->organisation->id
                ],

                'uploadRoutes' => [
                    'history' => [
                        'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet_returns.pallets.uploads.history',
                        'parameters' => [
                            'organisation'       => $palletReturn->organisation->slug,
                            'fulfilment'         => $palletReturn->fulfilment->slug,
                            'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->id,
                            'palletReturn'       => $palletReturn->reference
                        ]
                    ],
                    'download' => [
                        'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet_returns.pallets.uploads.templates',
                        'parameters' => [
                            'organisation'       => $palletReturn->organisation->slug,
                            'fulfilment'         => $palletReturn->fulfilment->slug,
                            'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->slug,
                            'palletReturn'       => $palletReturn->reference
                        ]
                    ],
                ],

                'palletRoute' => [
                    'index' => [
                        'name'       => 'grp.org.fulfilments.show.crm.customers.show.stored-pallets.index',
                        'parameters' => [
                            'organisation'       => $palletReturn->organisation->slug,
                            'fulfilment'         => $palletReturn->fulfilment->slug,
                            'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->slug
                        ]
                    ],
                    'store' => [
                        'name'       => 'grp.models.fulfilment-customer.pallet-return.pallet.store',
                        'parameters' => [
                            'organisation'       => $palletReturn->organisation->slug,
                            'fulfilment'         => $palletReturn->fulfilment->slug,
                            'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->id,
                            'palletReturn'       => $palletReturn->id
                        ]
                    ]
                ],

                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => PalletReturnTabsEnum::navigation($palletReturn)
                ],
                'data'             => PalletReturnResource::make($palletReturn),
                'box_stats'        => [
                    'fulfilment_customer'          => array_merge(
                        FulfilmentCustomerResource::make($palletReturn->fulfilmentCustomer)->getArray(),
                        [
                            'address'      => [
                                'value'   => AddressResource::make($palletReturn->deliveryAddress ?? new Address()),
                                'options' => [
                                    'countriesAddressData' => GetAddressData::run()
                                ]
                            ],
                            'addresses_list'   => $addressHistories,
                        ]
                    ),
                    'delivery_status'              => PalletReturnStateEnum::stateIcon()[$palletReturn->state->value],
                    'order_summary'                => [
                        'number_pallets'               => $palletReturn->number_pallets,
                        'number_services'              => $palletReturn->stats->number_services,
                        'number_physical_goods'        => $palletReturn->stats->number_physical_goods,
                        'pallets_price'                => 0,
                        'physical_goods_price'         => $palletReturn->physicalGoods->pluck('price'),
                        'services_price'               => $palletReturn->physicalGoods->pluck('price'),
                        'total_pallets_price'          => 0,
                        'total_services_price'         => $palletReturn->stats->total_services_price,
                        'total_physical_goods_price'   => $palletReturn->stats->total_physical_goods_price,
                        'total_price'                  => $palletReturn->stats->total_price
                    ]
                ],
                'notes_data'             => [
                    [
                        'label'           => __('Customer'),
                        'note'            => $palletReturn->customer_notes ?? '',
                        'editable'        => false,
                        'bgColor'         => 'blue',
                        'field'           => 'customer_notes'
                    ],
                    [
                        'label'           => __('Public'),
                        'note'            => $palletReturn->public_notes ?? '',
                        'editable'        => true,
                        'bgColor'         => 'pink',
                        'field'           => 'public_notes'
                    ],
                    [
                        'label'           => __('Private'),
                        'note'            => $palletReturn->internal_notes ?? '',
                        'editable'        => true,
                        'bgColor'         => 'purple',
                        'field'           => 'internal_notes'
                    ],
                ],

                'service_list_route'   => [
                    'name'       => 'grp.org.fulfilments.show.billables.services.index',
                    'parameters' => [
                        'organisation' => $palletReturn->organisation->slug,
                        'fulfilment'   => $palletReturn->fulfilment->slug
                    ]
                ],
                'physical_good_list_route'   => [
                    'name'       => 'grp.org.fulfilments.show.billables.outers.index',
                    'parameters' => [
                        'organisation' => $palletReturn->organisation->slug,
                        'fulfilment'   => $palletReturn->fulfilment->slug
                    ]
                ],

                PalletReturnTabsEnum::PALLETS->value => $this->tab == PalletReturnTabsEnum::PALLETS->value ?
                    fn () => PalletReturnItemsResource::collection(IndexPalletsInReturn::run($palletReturn))
                    : Inertia::lazy(fn () => PalletReturnItemsResource::collection(IndexPalletsInReturn::run($palletReturn))),

                PalletReturnTabsEnum::SERVICES->value => $this->tab == PalletReturnTabsEnum::SERVICES->value ?
                    fn () => ServicesResource::collection(IndexServiceInPalletReturn::run($palletReturn))
                    : Inertia::lazy(fn () => ServicesResource::collection(IndexServiceInPalletReturn::run($palletReturn))),

                PalletReturnTabsEnum::PHYSICAL_GOODS->value => $this->tab == PalletReturnTabsEnum::PHYSICAL_GOODS->value ?
                    fn () => PhysicalGoodsResource::collection(IndexPhysicalGoodInPalletReturn::run($palletReturn))
                    : Inertia::lazy(fn () => PhysicalGoodsResource::collection(IndexPhysicalGoodInPalletReturn::run($palletReturn))),
            ]
        )->table(
            IndexPalletsInReturn::make()->tableStructure(
                $palletReturn,
                prefix: PalletReturnTabsEnum::PALLETS->value
            )
        )->table(
            IndexServiceInPalletReturn::make()->tableStructure(
                $palletReturn,
                prefix: PalletReturnTabsEnum::SERVICES->value
            )
        )->table(
            IndexPhysicalGoodInPalletReturn::make()->tableStructure(
                $palletReturn,
                prefix: PalletReturnTabsEnum::PHYSICAL_GOODS->value
            )
        );
    }


    public function jsonResponse(PalletReturn $palletReturn): PalletReturnsResource
    {
        return new PalletReturnsResource($palletReturn);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, $suffix = ''): array
    {
        $headCrumb = function (PalletReturn $palletReturn, array $routeParameters, string $suffix) {
            return [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('Pallet returns')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $palletReturn->reference,
                        ],

                    ],
                    'suffix' => $suffix
                ],
            ];
        };

        $palletReturn = PalletReturn::where('slug', $routeParameters['palletReturn'])->first();

        return match ($routeName) {
            'grp.org.fulfilments.show.crm.customers.show.pallet_returns.show' => array_merge(
                ShowFulfilmentCustomer::make()->getBreadcrumbs(Arr::only($routeParameters, ['organisation', 'fulfilment', 'fulfilmentCustomer'])),
                $headCrumb(
                    $palletReturn,
                    [
                        'index' => [
                            'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet_returns.index',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment', 'fulfilmentCustomer'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet_returns.show',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment', 'fulfilmentCustomer', 'palletReturn'])
                        ]
                    ],
                    $suffix
                )
            ),
            'grp.org.fulfilments.show.operations.pallet-returns.show' => array_merge(
                ShowFulfilment::make()->getBreadcrumbs(Arr::only($routeParameters, ['organisation', 'fulfilment'])),
                $headCrumb(
                    $palletReturn,
                    [
                        'index' => [
                            'name'       => 'grp.org.fulfilments.show.operations.pallet-returns.index',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment', 'palletReturn'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.fulfilments.show.operations.pallet-returns.show',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment', 'palletReturn'])
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
                    $palletReturn,
                    [
                        'index' => [
                            'name'       => 'grp.org.warehouses.show.fulfilment.pallet-returns.index',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'warehouse'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.warehouses.show.fulfilment.pallet-returns.show',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'warehouse', 'palletReturn'])
                        ]
                    ],
                    $suffix
                ),
            ),

            default => []
        };
    }

    public function getPrevious(PalletReturn $palletReturn, ActionRequest $request): ?array
    {
        $previous = PalletReturn::where('id', '<', $palletReturn->id)->orderBy('id', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(PalletReturn $palletReturn, ActionRequest $request): ?array
    {
        $next = PalletReturn::where('id', '>', $palletReturn->id)->orderBy('id')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?PalletReturn $palletReturn, string $routeName): ?array
    {
        if (!$palletReturn) {
            return null;
        }


        return match (class_basename($this->parent)) {
            'Warehouse' => [
                'label' => $palletReturn->reference,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation'   => $palletReturn->organisation->slug,
                        'warehouse'      => $palletReturn->warehouse->slug,
                        'palletReturn'   => $palletReturn->reference
                    ]

                ]
            ],
            'FulfilmentCustomer' => [
                'label' => $palletReturn->reference,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation'       => $palletReturn->organisation->slug,
                        'fulfilment'         => $palletReturn->fulfilment->slug,
                        'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->slug,
                        'palletReturn'       => $palletReturn->reference
                    ]

                ]
            ],
            default => []
        };
    }
}
