<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Feb 2024 15:28:04 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Fulfilment\PalletDelivery\UI;

use App\Actions\Fulfilment\Pallet\UI\IndexPalletsInDelivery;
use App\Actions\Fulfilment\PalletDelivery\UI\IndexPhysicalGoodInPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\UI\IndexServiceInPalletDelivery;
use App\Actions\Fulfilment\UI\Catalogue\Rentals\IndexFulfilmentRentals;
use App\Actions\Retina\Fulfilment\UI\ShowRetinaStorageDashboard;
use App\Actions\RetinaAction;
use App\Enums\Fulfilment\FulfilmentTransaction\FulfilmentTransactionTypeEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Enums\UI\Fulfilment\PalletDeliveryTabsEnum;
use App\Http\Resources\Catalogue\RentalsResource;
use App\Http\Resources\Fulfilment\FulfilmentCustomerResource;
use App\Http\Resources\Fulfilment\FulfilmentTransactionsResource;
use App\Http\Resources\Fulfilment\PalletDeliveryResource;
use App\Http\Resources\Fulfilment\PalletsResource;
use App\Http\Resources\Helpers\CurrencyResource;
use App\Models\Fulfilment\PalletDelivery;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowRetinaPalletDelivery extends RetinaAction
{
    public function authorize(ActionRequest $request): bool
    {
        return $this->customer->fulfilmentCustomer->id === $request->route('palletDelivery')->fulfilment_customer_id;
    }


    public function asController(PalletDelivery $palletDelivery, ActionRequest $request): PalletDelivery
    {
        $this->initialisation($request)->withTab(PalletDeliveryTabsEnum::values());

        return $palletDelivery;
    }


    public function htmlResponse(PalletDelivery $palletDelivery, ActionRequest $request): Response
    {
        $palletsInDelivery = $palletDelivery->pallets()->count();

        $numberPallets       = $palletDelivery->fulfilmentCustomer->pallets()->count();
        $numberStoredPallets = $palletDelivery->pallets()->where('state', PalletDeliveryStateEnum::BOOKED_IN->value)->count();

        $totalPallets    = $numberPallets + $numberStoredPallets;
        $palletLimits    = $palletDelivery->fulfilmentCustomer->rentalAgreement->pallets_limit ?? 0;
        $palletLimitLeft = ($palletLimits - ($totalPallets + $numberStoredPallets));
        $palletLimitData = $palletLimits == null
            ? null
            : ($palletLimitLeft < 0
                ? [
                    'status'  => 'exceeded',
                    'message' => __("Pallet has reached over the limit: :palletLimitLeft", ['palletLimitLeft' => $palletLimitLeft])
                ]
                : ($palletLimitLeft == 0
                    ? [
                        'status'  => 'limit',
                        'message' => __("Pallet has reached the limit, no space left.")
                    ]
                    : ($palletLimitLeft <= 5
                        ? [
                            'status'  => 'almost',
                            'message' => __("Pallet almost reached the limit: :palletLimitLeft left.", ['palletLimitLeft' => $palletLimitLeft])
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


        $showGrossAndDiscount = $palletDelivery->gross_amount !== $palletDelivery->net_amount;


        $title = __('pallet delivery').' '.$palletDelivery->reference;

        return Inertia::render(
            'Storage/RetinaPalletDelivery',
            [
                'title'       => __('pallet delivery').' '.$palletDelivery->reference,
                'breadcrumbs' => $this->getBreadcrumbs($palletDelivery),
                'navigation'  => [
                    'previous' => $this->getPrevious($palletDelivery, $request),
                    'next'     => $this->getNext($palletDelivery, $request),
                ],
                'pageHead'    => [
                    'title' => $palletDelivery->state == PalletDeliveryStateEnum::IN_PROCESS
                        ? __('New pallet delivery')
                        : __('Pallet delivery'),
                    'icon'  => [
                        'icon'  => ['fal', 'fa-truck'],
                        'title' => $palletDelivery->reference
                    ],
                    'afterTitle'   => [
                        'label'     => $palletDelivery->reference,
                    ],

                    'actions' => $palletDelivery->state == PalletDeliveryStateEnum::IN_PROCESS
                        ? [
                            [
                                'type'   => 'buttonGroup',
                                'key'    => 'upload-add',
                                'button' => [
                                    [
                                        'type'  => 'button',
                                        'style' => 'secondary',
                                        'icon'  => ['fal', 'fa-upload'],
                                        'label' => 'upload',
                                    ],
                                    [
                                        'type'  => 'button',
                                        'style' => 'secondary',
                                        'icon'  => ['far', 'fa-layer-plus'],
                                        'label' => 'multiple',
                                        'route' => [
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
                                'type'     => 'button',
                                'icon'     => 'fad fa-save',
                                'tooltip'  => __('Submit Delivery'),
                                'label'    => __('submit'),
                                'disabled' => $palletsInDelivery == 0,
                                'key'      => 'action',
                                'route'    => [
                                    'method'     => 'post',
                                    'name'       => 'retina.models.pallet-delivery.submit',
                                    'parameters' => [
                                        'palletDelivery' => $palletDelivery->id
                                    ]
                                ]
                            ],
                        ]
                        : [
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


                'updateRoute' => [
                    'route' => [
                        'name'       => 'retina.models.pallet-delivery.update',
                        'parameters' => [
                            'palletDelivery' => $palletDelivery->id
                        ]
                    ]
                ],


                'interest'           => [
                    'pallets_storage' => $palletDelivery->fulfilmentCustomer->pallets_storage,
                    'items_storage'   => $palletDelivery->fulfilmentCustomer->items_storage,
                    'dropshipping'    => $palletDelivery->fulfilmentCustomer->dropshipping,
                ],
                'upload_spreadsheet' => [
                    'event'           => 'action-progress',
                    'channel'         => 'retina.personal.'.$palletDelivery->organisation->id,
                    'required_fields' => ['customer_reference', 'notes', 'stored_items', 'type'],
                    'template'        => [
                        'label' => 'Download template (.xlsx)',
                    ],
                    'route'           => [
                        'upload'   => [
                            'name'       => 'retina.models.pallet-delivery.pallet.upload',
                            'parameters' => [
                                'palletDelivery' => $palletDelivery->id
                            ]
                        ],
                        'history'  => [
                            'name'       => 'retina.fulfilment.storage.pallet_deliveries.pallets.uploads.history',
                            'parameters' => [
                                'palletDelivery' => $palletDelivery->slug
                            ]
                        ],
                        'download' => [
                            'name'       => 'retina.fulfilment.storage.pallet_deliveries.pallets.uploads.templates',
                            'parameters' => [
                                'palletDelivery' => $palletDelivery->slug
                            ]
                        ],
                    ],
                ],

                'locationRoute' => [
                    'name'       => 'grp.org.warehouses.show.infrastructure.locations.index',
                    'parameters' => [
                        'organisation' => $palletDelivery->organisation->slug,
                        'warehouse'    => $palletDelivery->warehouse->slug
                    ]
                ],

                'storedItemsRoute' => [
                    'index' => [
                        'name'       => 'retina.fulfilment.storage.stored-items.index',
                        'parameters' => []
                    ],
                    'store' => [
                        'name'       => 'retina.models.stored-items.store',
                        'parameters' => []
                    ]
                ],

                'rental_lists' => $rentalList,

                'service_list_route'       => [
                    'name'       => 'retina.json.fulfilment.delivery.services.index',
                    'parameters' => [
                        'fulfilment' => $palletDelivery->fulfilment->slug,
                        'scope'      => $palletDelivery->slug
                    ]
                ],
                'physical_good_list_route' => [
                    'name'       => 'retina.json.fulfilment.delivery.physical-goods.index',
                    'parameters' => [
                        'fulfilment' => $palletDelivery->fulfilment->slug,
                        'scope'      => $palletDelivery->slug
                    ]
                ],

                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => PalletDeliveryTabsEnum::navigation($palletDelivery)
                ],

                'pallet_limits' => $palletLimitData,

                'data'       => PalletDeliveryResource::make($palletDelivery),
                'box_stats'  => [
                    'fulfilment_customer' => FulfilmentCustomerResource::make($palletDelivery->fulfilmentCustomer)->getArray(),
                    'delivery_state'      => PalletDeliveryStateEnum::stateIcon()[$palletDelivery->state->value],
                    'order_summary'       => [
                        [
                            [
                                'label'       => __('Services'),
                                'quantity'    => $palletDelivery->stats->number_services ?? 0,
                                'price_base'  => __('Multiple'),
                                'price_total' => $servicesNet
                            ],
                            [
                                'label'       => __('Physical Goods'),
                                'quantity'    => $palletDelivery->stats->number_physical_goods ?? 0,
                                'price_base'  => __('Multiple'),
                                'price_total' => $physicalGoodsNet
                            ],
                        ],
                        $showGrossAndDiscount ? [
                            [
                                'label'       => __('Gross'),
                                'information' => '',
                                'price_total' => $palletDelivery->gross_amount
                            ],
                            [
                                'label'       => __('Discounts'),
                                'information' => '',
                                'price_total' => $palletDelivery->discount_amount
                            ],
                        ] : [],
                        [
                            [
                                'label'       => __('Net'),
                                'information' => '',
                                'price_total' => $palletDelivery->net_amount
                            ],
                            [
                                'label'       => __('Tax').' '.$palletDelivery->taxCategory->rate * 100 .'%',
                                'information' => '',
                                'price_total' => $palletDelivery->tax_amount
                            ],
                        ],
                        [
                            [
                                'label'       => __('Total'),
                                'price_total' => $palletDelivery->total_amount
                            ],
                        ],

                        'currency' => CurrencyResource::make($palletDelivery->currency),
                    ]
                ],
                'notes_data' => [
                    [
                        'label'    => __("Notes"),
                        'note'     => $palletDelivery->customer_notes ?? '',
                        'editable' => true,
                        'field'    => 'customer_notes'
                    ],

                ],

                PalletDeliveryTabsEnum::PALLETS->value => $this->tab == PalletDeliveryTabsEnum::PALLETS->value ?
                    fn () => PalletsResource::collection(IndexPalletsInDelivery::run($palletDelivery, PalletDeliveryTabsEnum::PALLETS->value))
                    : Inertia::lazy(fn () => PalletsResource::collection(IndexPalletsInDelivery::run($palletDelivery, PalletDeliveryTabsEnum::PALLETS->value))),

                PalletDeliveryTabsEnum::SERVICES->value => $this->tab == PalletDeliveryTabsEnum::SERVICES->value ?
                    fn () => FulfilmentTransactionsResource::collection(IndexServiceInPalletDelivery::run($palletDelivery, PalletDeliveryTabsEnum::SERVICES->value))
                    : Inertia::lazy(fn () => FulfilmentTransactionsResource::collection(IndexServiceInPalletDelivery::run($palletDelivery, PalletDeliveryTabsEnum::SERVICES->value))),

                PalletDeliveryTabsEnum::PHYSICAL_GOODS->value => $this->tab == PalletDeliveryTabsEnum::PHYSICAL_GOODS->value ?
                    fn () => FulfilmentTransactionsResource::collection(IndexPhysicalGoodInPalletDelivery::run($palletDelivery, PalletDeliveryTabsEnum::PHYSICAL_GOODS->value))
                    : Inertia::lazy(fn () => FulfilmentTransactionsResource::collection(IndexPhysicalGoodInPalletDelivery::run($palletDelivery, PalletDeliveryTabsEnum::PHYSICAL_GOODS->value))),
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


    public function getBreadcrumbs(PalletDelivery $palletDelivery, $suffix = ''): array
    {
        $headCrumb = function (PalletDelivery $palletDelivery, array $routeParameters, string $suffix) {
            return [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('Pallet deliveries')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $palletDelivery->slug,
                        ],

                    ],
                    'suffix'         => $suffix
                ],
            ];
        };


        return array_merge(
            ShowRetinaStorageDashboard::make()->getBreadcrumbs(),
            $headCrumb(
                $palletDelivery,
                [
                    'index' => [
                        'name'       => 'retina.fulfilment.storage.pallet_deliveries.index',
                        'parameters' => []
                    ],
                    'model' => [
                        'name'       => 'retina.fulfilment.storage.pallet_deliveries.show',
                        'parameters' => [$palletDelivery->slug]
                    ]
                ],
                $suffix
            ),
        );
    }

    public function getPrevious(PalletDelivery $palletDelivery, ActionRequest $request): ?array
    {
        $previous = PalletDelivery::where('fulfilment_customer_id', $this->customer->fulfilmentCustomer->id)->where('id', '<', $palletDelivery->id)->orderBy('id', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(PalletDelivery $palletDelivery, ActionRequest $request): ?array
    {
        $next = PalletDelivery::where('fulfilment_customer_id', $this->customer->fulfilmentCustomer->id)->where('id', '>', $palletDelivery->id)->orderBy('id')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?PalletDelivery $palletDelivery, string $routeName): ?array
    {
        if (!$palletDelivery) {
            return null;
        }


        return match ($routeName) {
            'retina.fulfilment.storage.pallet_deliveries.show' => [
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
