<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Feb 2024 15:28:04 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Storage\PalletDelivery\UI;

use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilmentRentals;
use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\Pallet\UI\IndexPalletsInDelivery;
use App\Actions\Fulfilment\PalletDelivery\UI\IndexPhysicalGoodInPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\UI\IndexServiceInPalletDelivery;
use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\RetinaAction;
use App\Actions\UI\Retina\Storage\UI\ShowStorageDashboard;
use App\Enums\Fulfilment\FulfilmentTransaction\FulfilmentTransactionTypeEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Enums\UI\Fulfilment\PalletDeliveryTabsEnum;
use App\Http\Resources\Catalogue\RentalsResource;
use App\Http\Resources\Fulfilment\FulfilmentCustomerResource;
use App\Http\Resources\Fulfilment\FulfilmentTransactionResource;
use App\Http\Resources\Fulfilment\PalletDeliveryResource;
use App\Http\Resources\Fulfilment\PalletsResource;
use App\Models\Fulfilment\PalletDelivery;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowPalletDelivery extends RetinaAction
{
    public function handle(PalletDelivery $palletDelivery): PalletDelivery
    {
        return $palletDelivery;
    }



    public function asController(PalletDelivery $palletDelivery, ActionRequest $request): PalletDelivery
    {
        $this->initialisation($request)->withTab(PalletDeliveryTabsEnum::values());

        return $this->handle($palletDelivery);
    }



    public function htmlResponse(PalletDelivery $palletDelivery, ActionRequest $request): Response
    {

        $numberPallets       = $palletDelivery->fulfilmentCustomer->pallets()->count();
        $numberStoredPallets = $palletDelivery->pallets()->where('state', PalletDeliveryStateEnum::BOOKED_IN->value)->count();

        $totalPallets    = $numberPallets + $numberStoredPallets;
        $palletLimits    = $palletDelivery->fulfilmentCustomer->rentalAgreement->pallets_limit ?? 0;
        $palletLimitLeft = ($palletLimits - ($totalPallets + $numberStoredPallets));
        $palletLimitData = $palletLimits == null ? null : ($palletLimitLeft < 0
        ? [
                'status'  => 'exceeded',
                'message' => __("Pallet has reached over the limit: $palletLimitLeft.")
            ]
        : ($palletLimitLeft == 0
            ? [
                    'status'  => 'limit',
                    'message' => __("Pallet has reached the limit, no space left.")
                ]
            : ($palletLimitLeft <= 5
                ? [
                        'status'  => 'almost',
                        'message' => __("Pallet almost reached the limit: $palletLimitLeft left.")
                    ]
                : null)));

        $rentalList = [];

        if (in_array($palletDelivery->state, [PalletDeliveryStateEnum::BOOKING_IN, PalletDeliveryStateEnum::BOOKED_IN])) {
            $rentalList = RentalsResource::collection(IndexFulfilmentRentals::run($palletDelivery->fulfilment, 'rentals'))->toArray($request);
        }

        $physicalGoods    = $palletDelivery->transactions()->where('type', FulfilmentTransactionTypeEnum::PRODUCT)->get();
        $physicalGoodsNet = $physicalGoods->sum('net_amount');
        $services         = $palletDelivery->transactions()->where('type', FulfilmentTransactionTypeEnum::SERVICE)->get();
        $servicesNet      = $services->sum('net_amount');
        $palletPriceTotal = 0;
        foreach ($palletDelivery->pallets as $pallet) {
            $rentalPrice = $pallet->rental->price ?? 0;
            $palletPriceTotal += $rentalPrice;
        }

        return Inertia::render(
            'Storage/RetinaPalletDelivery',
            [
                'title'       => __('pallet delivery'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation' => [
                    'previous' => $this->getPrevious($palletDelivery, $request),
                    'next'     => $this->getNext($palletDelivery, $request),
                ],
                'pageHead' => [
                    'title'     => $palletDelivery->reference,
                    'icon'      => [
                        'icon'  => ['fal', 'fa-truck-couch'],
                        'title' => $palletDelivery->reference
                    ],
                    'model'     => __('Pallet Delivery'),
                    'edit'      => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                            'parameters' => array_values($request->route()->originalParameters())
                        ]
                    ] : false,
                    'actions' => $palletDelivery->state == PalletDeliveryStateEnum::IN_PROCESS ? [
                        [
                            'type'   => 'buttonGroup',
                            'key'    => 'upload-add',
                            'button' => [
                                // [
                                //     'type'  => 'button',
                                //     'style' => 'secondary',
                                //     'icon'  => ['fal', 'fa-upload'],
                                //     'label' => 'upload',
                                //     'route' => [
                                //         'name'       => 'retina.models.pallet-delivery.pallet.upload',
                                //         'parameters' => [
                                //             'palletDelivery' => $palletDelivery->id
                                //         ]
                                //     ]
                                // ],
                                [
                                    'type'    => 'button',
                                    'style'   => 'secondary',
                                    'icon'    => ['far', 'fa-layer-plus'],
                                    'label'   => 'multiple',
                                    'route'   => [
                                        'name'       => 'retina.models.pallet-delivery.multiple-pallets.store',
                                        'parameters' => [
                                            'palletDelivery' => $palletDelivery->id
                                        ]
                                    ]
                                ],
                                [
                                    'type'  => 'button',
                                    'style' => 'secondary',
                                    'icon'  => 'fal fa-plus',
                                    'label' => __('add pallet'),
                                    'route' => [
                                        'name'       => 'retina.models.pallet-delivery.pallet.store',
                                        'parameters' => [
                                            'palletDelivery' => $palletDelivery->id
                                        ]
                                    ]
                                ],
                                [
                                    'type'  => 'button',
                                    'style' => 'secondary',
                                    'icon'  => 'fal fa-plus',
                                    'label' => __('add service'),
                                    'route' => [
                                        'name'       => 'retina.models.pallet-delivery.transaction.store',
                                        'parameters' => [
                                            'palletDelivery' => $palletDelivery->id
                                        ]
                                    ]
                                ],
                                [
                                    'type'  => 'button',
                                    'style' => 'secondary',
                                    'icon'  => 'fal fa-plus',
                                    'label' => __('add physical good'),
                                    'route' => [
                                        'name'       => 'retina.models.pallet-delivery.transaction.store',
                                        'parameters' => [
                                            'palletDelivery' => $palletDelivery->id
                                        ]
                                    ]
                                ],
                            ]
                        ],
                        [
                            'type'    => 'button',
                            'icon'    => 'fad fa-save',
                            'tooltip' => ($palletDelivery->pallets()->count() > 0) ? __('Submit Delivery') : __('Add pallet to submit Delivery'),
                            'label'   => __('submit'),
                            'disabled'=> ($palletDelivery->pallets()->count() > 0) ? false : true,
                            'key'     => 'action',
                            'route'   => [
                                'method'     => 'post',
                                'name'       => 'retina.models.pallet-delivery.submit',
                                'parameters' => [
                                    'palletDelivery' => $palletDelivery->id
                                ]
                            ]
                        ],
                    ] : [
                        [
                            'type'   => 'button',
                            'style'  => 'tertiary',
                            'label'  => 'PDF',
                            'target' => '_blank',
                            'icon'   => 'fal fa-file-pdf',
                            'key'    => 'action',
                            'route'  => [
                                'name'       => 'retina.models.pallet-delivery.pdf',
                                'parameters' => [
                                    'palletDelivery' => $palletDelivery->id
                                ],
                            ]
                        ]
                    ]
                ],

                'box_stats'        => [
                    'delivery_status'   => PalletDeliveryStateEnum::stateIcon()[$palletDelivery->state->value],
                ],

                'updateRoute' => [
                    'route' => [
                        'name'       => 'retina.models.pallet-delivery.update',
                        'parameters' => [
                            'palletDelivery'     => $palletDelivery->id
                        ]
                    ]
                ],

                'uploadRoutes' => [
                    'history' => [
                        'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet_deliveries.pallets.uploads.history',
                        'parameters' => [
                            'organisation'       => $palletDelivery->organisation->slug,
                            'fulfilment'         => $palletDelivery->fulfilment->slug,
                            'fulfilmentCustomer' => $palletDelivery->fulfilmentCustomer->id,
                            'palletDelivery'     => $palletDelivery->slug
                        ]
                    ],
                    'download' => [
                        'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet_deliveries.pallets.uploads.templates',
                        'parameters' => [
                            'organisation'       => $palletDelivery->organisation->slug,
                            'fulfilment'         => $palletDelivery->fulfilment->slug,
                            'fulfilmentCustomer' => $palletDelivery->fulfilmentCustomer->slug,
                            'palletDelivery'     => $palletDelivery->slug
                        ]
                    ],
                ],

                'locationRoute' => [
                    'name'       => 'grp.org.warehouses.show.infrastructure.locations.index',
                    'parameters' => [
                        'organisation'       => $palletDelivery->organisation->slug,
                        'warehouse'          => $palletDelivery->warehouse->slug
                    ]
                ],

                'storedItemsRoute' => [
                    'index' => [
                        'name'       => 'retina.storage.stored-items.index',
                        'parameters' => []
                    ],
                    'store' => [
                        'name'       => 'retina.models.stored-items.store',
                        'parameters' => []
                    ]
                ],

                'rental_lists'         => $rentalList,

                'service_list_route'   => [
                    'name'       => 'retina.json.fulfilment.delivery.services.index',
                    'parameters' => [
                        'fulfilment'     => $palletDelivery->fulfilment->slug,
                        'scope'          => $palletDelivery->slug
                    ]
                ],
                'physical_good_list_route'   => [
                    'name'       => 'retina.json.fulfilment.physical-goods.index',
                    'parameters' => [
                        'fulfilment'     => $palletDelivery->fulfilment->slug,
                    ]
                ],

                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => PalletDeliveryTabsEnum::navigation($palletDelivery)
                ],

                'pallet_limits' => $palletLimitData,

                'data'             => PalletDeliveryResource::make($palletDelivery),
                'box_stats'        => [
                    'fulfilment_customer'          => FulfilmentCustomerResource::make($palletDelivery->fulfilmentCustomer)->getArray(),
                    'delivery_status'              => PalletDeliveryStateEnum::stateIcon()[$palletDelivery->state->value],
                    'order_summary'                => [
                        [
                            [
                                'label'         => __('Pallets'),
                                'quantity'      => $palletDelivery->stats->number_pallets ?? 0,
                                'price_base'    => __('Multiple'),
                                'price_total'   => ceil($palletPriceTotal) ?? 0
                            ],
                            [
                                'label'         => __('Services'),
                                'quantity'      => $palletDelivery->stats->number_services ?? 0,
                                'price_base'    => __('Multiple'),
                                'price_total'   => $servicesNet
                            ],
                            [
                                'label'         => __('Physical Goods'),
                                'quantity'      => $palletDelivery->stats->number_physical_goods ?? 0,
                                'price_base'    => __('Multiple'),
                                'price_total'   => $physicalGoodsNet
                            ],
                        ],
                        [
                            [
                                'label'         => __('Shipping'),
                                'information'   => __('Shipping fee to your address using DHL service.'),
                                'price_total'   => 1111
                            ],
                            [
                                'label'         => __('Tax'),
                                'information'   => __('Tax is based on 10% of total order.'),
                                'price_total'   => $palletDelivery->taxCategory->rate
                            ],
                        ],
                        [
                            [
                                'label'         => __('Total'),
                                'price_total'   => ceil($servicesNet + $physicalGoodsNet + $palletPriceTotal + $palletDelivery->taxCategory->rate)
                            ],
                        ],
                        // 'currency_code'                => 'usd',  // TODO
                        // // 'number_pallets'               => $palletDelivery->stats->number_pallets,
                        // // 'number_services'              => $palletDelivery->stats->number_services,
                        // // 'number_physical_goods'        => $palletDelivery->stats->number_physical_goods,
                        // 'pallets_price'                => 0,  // TODO
                        // 'physical_goods_price'         => $physicalGoodsNet,
                        // 'services_price'               => $servicesNet,
                        // 'total_pallets_price'          => 0,  // TODO
                        // // 'total_services_price'         => $palletDelivery->stats->total_services_price,
                        // // 'total_physical_goods_price'   => $palletDelivery->stats->total_physical_goods_price,
                        // 'shipping'                     => [
                        //     'tooltip'           => __('Shipping fee to your address using DHL service.'),
                        //     'fee'               => 11111, // TODO
                        // ],
                        // 'tax'                      => [
                        //     'tooltip'           => __('Tax is based on 10% of total order.'),
                        //     'fee'               => 99999, // TODO
                        // ],
                        // 'total_price'                  => $palletDelivery->stats->total_price
                    ]
                ],
                'notes_data'             => [
                    [
                        'label'           => __("Delivery's note"),
                        'note'            => $palletDelivery->customer_notes ?? '',
                        'editable'        => true,
                        // 'bgColor'         => 'blue',
                        'field'           => 'customer_notes'
                    ],
                    [
                        'label'           => __('Note from warehouse'),
                        'note'            => $palletDelivery->public_notes ?? '',
                        'editable'        => false,
                        // 'bgColor'         => 'pink',
                        'field'           => 'public_notes'
                    ],
                ],

                PalletDeliveryTabsEnum::PALLETS->value => $this->tab == PalletDeliveryTabsEnum::PALLETS->value ?
                    fn () => PalletsResource::collection(IndexPalletsInDelivery::run($palletDelivery, PalletDeliveryTabsEnum::PALLETS->value))
                    : Inertia::lazy(fn () => PalletsResource::collection(IndexPalletsInDelivery::run($palletDelivery, PalletDeliveryTabsEnum::PALLETS->value))),

                PalletDeliveryTabsEnum::SERVICES->value => $this->tab == PalletDeliveryTabsEnum::SERVICES->value ?
                    fn () => FulfilmentTransactionResource::collection(IndexServiceInPalletDelivery::run($palletDelivery, PalletDeliveryTabsEnum::SERVICES->value))
                    : Inertia::lazy(fn () => FulfilmentTransactionResource::collection(IndexServiceInPalletDelivery::run($palletDelivery, PalletDeliveryTabsEnum::SERVICES->value))),

                PalletDeliveryTabsEnum::PHYSICAL_GOODS->value => $this->tab == PalletDeliveryTabsEnum::PHYSICAL_GOODS->value ?
                    fn () => FulfilmentTransactionResource::collection(IndexPhysicalGoodInPalletDelivery::run($palletDelivery, PalletDeliveryTabsEnum::PHYSICAL_GOODS->value))
                    : Inertia::lazy(fn () => FulfilmentTransactionResource::collection(IndexPhysicalGoodInPalletDelivery::run($palletDelivery, PalletDeliveryTabsEnum::PHYSICAL_GOODS->value))),
            ]
        )->table(
            IndexPalletsInDelivery::make()->tableStructure(
                $palletDelivery,
                prefix: PalletDeliveryTabsEnum::PALLETS->value
            )
        )->table(
            IndexServiceInPalletDelivery::make()->tableStructure(
                $palletDelivery,
                prefix: PalletDeliveryTabsEnum::SERVICES->value
            )
        )->table(
            IndexPhysicalGoodInPalletDelivery::make()->tableStructure(
                $palletDelivery,
                prefix: PalletDeliveryTabsEnum::PHYSICAL_GOODS->value
            )
        );
    }


    public function jsonResponse(PalletDelivery $palletDelivery): PalletDeliveryResource
    {
        return new PalletDeliveryResource($palletDelivery);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, $suffix = ''): array
    {
        $headCrumb = function (PalletDelivery $palletDelivery, array $routeParameters, string $suffix) {
            return [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('pallet deliveries')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $palletDelivery->slug,
                        ],

                    ],
                    'suffix' => $suffix
                ],
            ];
        };

        $palletDelivery = PalletDelivery::where('slug', $routeParameters['palletDelivery'])->first();


        return match ($routeName) {
            'grp.org.fulfilments.show.crm.customers.show.pallet_deliveries.show' => array_merge(
                ShowFulfilmentCustomer::make()->getBreadcrumbs(Arr::only($routeParameters, ['organisation', 'fulfilment', 'fulfilmentCustomer'])),
                $headCrumb(
                    $palletDelivery,
                    [
                        'index' => [
                            'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet_deliveries.index',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment', 'fulfilmentCustomer'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet_deliveries.show',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment', 'fulfilmentCustomer', 'palletDelivery'])
                        ]
                    ],
                    $suffix
                )
            ),
            'grp.org.warehouses.show.fulfilment.pallet-deliveries.show' => array_merge(
                ShowWarehouse::make()->getBreadcrumbs(
                    Arr::only($routeParameters, ['organisation', 'warehouse'])
                ),
                $headCrumb(
                    $palletDelivery,
                    [
                        'index' => [
                            'name'       => 'grp.org.warehouses.show.fulfilment.pallet-deliveries.index',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'warehouse'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.warehouses.show.fulfilment.pallet-deliveries.show',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'warehouse', 'palletDelivery'])
                        ]
                    ],
                    $suffix
                ),
            ),
            'retina.storage.pallet-deliveries.show' => array_merge(
                ShowStorageDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    $palletDelivery,
                    [
                        'index' => [
                            'name'       => 'retina.storage.pallet-deliveries.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'retina.storage.pallet-deliveries.show',
                            'parameters' => [$palletDelivery->slug]
                        ]
                    ],
                    $suffix
                ),
            ),

            default => []
        };
    }

    public function getPrevious(PalletDelivery $palletDelivery, ActionRequest $request): ?array
    {
        $previous = PalletDelivery::where('id', '<', $palletDelivery->id)->orderBy('id', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(PalletDelivery $palletDelivery, ActionRequest $request): ?array
    {
        $next = PalletDelivery::where('id', '>', $palletDelivery->id)->orderBy('id')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?PalletDelivery $palletDelivery, string $routeName): ?array
    {
        if (!$palletDelivery) {
            return null;
        }


        return match ($routeName) {
            'retina.storage.pallet-deliveries.show' => [
                'label' => $palletDelivery->reference,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'palletDelivery' => $palletDelivery->slug
                    ]

                ]
            ]
        };
    }
}
