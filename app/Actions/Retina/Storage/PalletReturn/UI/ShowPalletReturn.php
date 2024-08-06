<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 17:41:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Storage\PalletReturn\UI;

use App\Actions\Fulfilment\Pallet\UI\IndexPalletsInReturn;
use App\Actions\Fulfilment\PalletReturn\UI\IndexPhysicalGoodInPalletReturn;
use App\Actions\Fulfilment\PalletReturn\UI\IndexServiceInPalletReturn;
use App\Actions\Fulfilment\StoredItem\UI\IndexStoredItemsInReturn;
use App\Actions\Helpers\Country\UI\GetAddressData;
use App\Actions\RetinaAction;
use App\Actions\UI\Retina\Storage\UI\ShowStorageDashboard;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Enums\Fulfilment\PalletReturn\PalletReturnTypeEnum;
use App\Enums\UI\Fulfilment\PalletReturnTabsEnum;
use App\Http\Resources\Fulfilment\FulfilmentCustomerResource;
use App\Http\Resources\Fulfilment\FulfilmentTransactionResource;
use App\Http\Resources\Fulfilment\PalletReturnItemsResource;
use App\Http\Resources\Fulfilment\PalletReturnResource;
use App\Http\Resources\Fulfilment\PalletReturnsResource;
use App\Http\Resources\Fulfilment\PalletReturnStoredItemsResource;
use App\Http\Resources\Helpers\AddressResource;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Helpers\Address;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowPalletReturn extends RetinaAction
{
    private FulfilmentCustomer $parent;

    public function handle(PalletReturn $palletReturn): PalletReturn
    {
        return $palletReturn;
    }


    public function asController(PalletReturn $palletReturn, ActionRequest $request): PalletReturn
    {
        $this->parent = $request->user()->customer->fulfilmentCustomer;
        $this->initialisation($request)->withTab(PalletReturnTabsEnum::values());

        return $this->handle($palletReturn);
    }

    public function htmlResponse(PalletReturn $palletReturn, ActionRequest $request): Response
    {
        $addressHistories = AddressResource::collection($palletReturn->addresses()->where('scope', 'delivery')->get());

        $navigation=PalletReturnTabsEnum::navigation($palletReturn);

        if($palletReturn->type==PalletReturnTypeEnum::PALLET) {
            unset($navigation[PalletReturnTabsEnum::STORED_ITEMS->value]);
        } else {
            unset($navigation[PalletReturnTabsEnum::PALLETS->value]);
        }

        if($palletReturn->type==PalletReturnTypeEnum::PALLET) {
            $this->tab = PalletReturnTabsEnum::PALLETS->value;
        } else {
            $this->tab = PalletReturnTabsEnum::STORED_ITEMS->value;
        }

        if($palletReturn->type==PalletReturnTypeEnum::STORED_ITEM) {
            $afterTitle=[
                'label'=> '('.__('Stored items').')'
                ];
        } else {
            $afterTitle=[
                'label'=> '('.__('Whole pallets').')'
            ];
        }

        return Inertia::render(
            'Storage/RetinaPalletReturn',
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
                    'title'     => $palletReturn->reference,
                    'icon'      => [
                        'icon'  => ['fal', 'fa-truck-couch'],
                        'title' => $palletReturn->reference
                    ],
                    'afterTitle'=> $afterTitle,
                    'model'     => __('pallet return'),
                    'actions'   => $palletReturn->state == PalletReturnStateEnum::IN_PROCESS ? [
                        [
                            'type'      => 'button',
                            'style'     => 'tertiary',
                            'icon'      => 'fal fa-upload',
                            'label'     => __('upload'),
                            'tooltip'   => __('Upload file')
                        ],
                        [
                            'type'   => 'buttonGroup',
                            'key'    => 'upload-add',
                            'button' => [
                                [
                                    'type'  => 'button',
                                    'style' => 'secondary',
                                    'icon'  => 'fal fa-plus',
                                    'label' => __('add pallet'),
                                    'route' => [
                                        'name'       => 'retina.models.pallet-return.pallet.store',
                                        'parameters' => [
                                            'palletReturn'       => $palletReturn->id
                                        ]
                                    ]
                                ],
                                [
                                    'type'    => 'button',
                                    'style'   => 'secondary',
                                    'icon'    => 'fal fa-plus',
                                    'label'   => __('add Stored Item'),
                                ],
                                [
                                    'type'  => 'button',
                                    'style' => 'secondary',
                                    'icon'  => 'fal fa-plus',
                                    'label' => __('add service'),
                                    'route' => [
                                        'name'       => 'retina.models.pallet-return.transaction.store',
                                        'parameters' => [
                                            'palletReturn'       => $palletReturn->id
                                        ]
                                    ]
                                ],
                                [
                                    'type'  => 'button',
                                    'style' => 'secondary',
                                    'icon'  => 'fal fa-plus',
                                    'label' => __('add physical good'),
                                    'route' => [
                                        'name'       => 'retina.models.pallet-return.transaction.store',
                                        'parameters' => [
                                            'palletReturn'       => $palletReturn->id
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
                                'name'       => 'retina.models.pallet-return.submit',
                                'parameters' => [
                                    'palletReturn'       => $palletReturn->id
                                ]
                            ]
                        ] : [],
                    ] : [
                        $palletReturn->state != PalletReturnStateEnum::DISPATCHED && $palletReturn->state != PalletReturnStateEnum::CANCEL ? [
                            'type'    => 'button',
                            'style'   => 'negative',
                            'icon'    => 'fal fa-times',
                            'tooltip' => __('cancel'),
                            'label'   => __('cancel return'),
                            'key'     => 'action',
                            'route'   => [
                                'method'     => 'post',
                                'name'       => 'retina.models.pallet-return.cancel',
                                'parameters' => [
                                    'palletReturn'       => $palletReturn->id
                                ]
                            ]
                        ] : []
                    ],
                ],

                'service_list_route'   => [
                    'name'       => 'retina.json.fulfilment.return.services.index',
                    'parameters' => [
                        'fulfilment'     => $palletReturn->fulfilment->slug,
                        'scope'          => $palletReturn->slug
                    ]
                ],
                'physical_good_list_route'   => [
                    'name'       => 'retina.json.fulfilment.return.physical-goods.index',
                    'parameters' => [
                        'fulfilment'     => $palletReturn->fulfilment->slug,
                        'scope'          => $palletReturn->slug
                    ]
                ],


                'updateRoute' => [
                    'route' => [
                        'name'       => 'retina.models.pallet-return.update',
                        'parameters' => [
                            'palletReturn'       => $palletReturn->id
                        ]
                    ]
                ],

                'deleteServiceRoute' => [
                    'name'       => 'retina.models.pallet-return.service.delete',
                    'parameters' => [
                        'palletReturn' => $palletReturn->id
                    ]
                ],

                'deletePhysicalGoodRoute' => [
                    'name'       => 'retina.models.pallet-return.physical_good.delete',
                    'parameters' => [
                        'palletReturn' => $palletReturn->id
                    ]
                ],

                'interest'  => [
                    'pallets_storage' => $palletReturn->fulfilmentCustomer->pallets_storage,
                    'items_storage'   => $palletReturn->fulfilmentCustomer->items_storage,
                    'dropshipping'    => $palletReturn->fulfilmentCustomer->dropshipping,
                ],
                'upload_spreadsheet' => [
                    'event'             => 'action-progress',
                    'channel'           => 'retina.personal.' . $palletReturn->organisation_id,
                    'required_fields'   => ['pallet_stored_item', 'pallet', 'stored_item', 'quantity'],
                    'template'          => [
                        'label' => 'Download template (.xlsx)',
                    ],
                    'route' => [
                        'upload'  => [
                            'name'       => 'retina.models.pallet-return.stored-item.upload',
                            'parameters' => [
                                'palletReturn' => $palletReturn->id
                            ]
                        ],
                        'history' => [
                            'name'       => 'retina.storage.pallet-returns.uploads.history',
                            'parameters' => [
                                'palletReturn'     => $palletReturn->slug
                            ]
                        ],
                        'download' => [
                            'name'       => 'retina.storage.pallet-returns.stored-items.uploads.templates',
                            'parameters' => [
                                'fulfilmentCustomer'     => $palletReturn->fulfilmentCustomer->slug,
                                'type'                   => 'xlsx'
                            ]
                        ],
                    ],
                ],

                'palletRoute' => [
                    'index' => [
                        'name'       => 'retina.json.fulfilment.return.pallets',
                        'parameters' => []
                    ],
                    'store' => [
                        'name'       => 'retina.models.pallet-return.pallet.store',
                        'parameters' => [
                            'palletReturn'       => $palletReturn->id
                        ]
                    ]
                ],

                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => $navigation
                ],
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
                        [
                            [
                                'label'         => __('Pallets'),
                                'quantity'      => $palletReturn->stats->number_pallets ?? 0,
                                'price_base'    => 999,
                                'price_total'   => 1111 ?? 0
                            ],
                            [
                                'label'         => __('Services'),
                                'quantity'      => $palletReturn->stats->number_services ?? 0,
                                'price_base'    => __('Multiple'),
                                'price_total'   => $palletReturn->stats->total_services_price ?? 0
                            ],
                            [
                                'label'         => __('Physical Goods'),
                                'quantity'      => $palletReturn->stats->number_physical_goods ?? 0,
                                'price_base'    => __('Multiple'),
                                'price_total'   => $palletReturn->stats->total_physical_goods_price ?? 0
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
                                'price_total'   => 1111
                            ],
                        ],
                        [
                            [
                                'label'         => __('Total'),
                                'price_total'   => $palletReturn->stats->total_price
                            ],
                        ],

                        // 'currency_code'                => 'usd',  // TODO
                        // 'number_pallets'               => $palletReturn->stats->number_pallets,
                        // 'number_services'              => $palletReturn->stats->number_services,
                        // 'number_physical_goods'        => $palletReturn->stats->number_physical_goods,
                        // 'pallets_price'                => 0,  // TODO
                        // 'physical_goods_price'         => 0,  // TODO
                        // 'services_price'               => 0,  // TODO
                        // 'total_pallets_price'          => 0,  // TODO
                        // 'total_services_price'         => $palletReturn->stats->total_services_price,
                        // 'total_physical_goods_price'   => $palletReturn->stats->total_physical_goods_price,
                        // 'shipping'                     => [
                        //     'tooltip'           => __('Shipping fee to your address using DHL service.'),
                        //     'fee'               => 11111, // TODO
                        // ],
                        // 'tax'                      => [
                        //     'tooltip'           => __('Tax is based on 10% of total order.'),
                        //     'fee'               => 99999, // TODO
                        // ],
                        // 'total_price'                  => $palletReturn->stats->total_price
                    ]
                ],
                'notes_data'             => [
                    'return'        => [
                        'label'           => __("Return's note"),
                        'note'            => $palletReturn->customer_notes ?? '',
                        'editable'        => true,
                        // 'bgColor'         => 'blue',
                        'field'           => 'customer_notes'
                    ],
                    'warehouse'     => [
                        'label'           => __('Note from warehouse'),
                        'note'            => $palletReturn->public_notes ?? '',
                        'editable'        => false,
                        // 'bgColor'         => 'pink',
                        'field'           => 'public_notes'
                    ],
                ],

                'data' => PalletReturnResource::make($palletReturn),

                PalletReturnTabsEnum::PALLETS->value => $this->tab == PalletReturnTabsEnum::PALLETS->value ?
                    fn () => PalletReturnItemsResource::collection(IndexPalletsInReturn::run($palletReturn))
                    : Inertia::lazy(fn () => PalletReturnItemsResource::collection(IndexPalletsInReturn::run($palletReturn))),

                PalletReturnTabsEnum::STORED_ITEMS->value => $this->tab == PalletReturnTabsEnum::STORED_ITEMS->value ?
                    fn () => PalletReturnStoredItemsResource::collection(IndexStoredItemsInReturn::run($palletReturn))
                    : Inertia::lazy(fn () => PalletReturnStoredItemsResource::collection(IndexStoredItemsInReturn::run($palletReturn))),

                PalletReturnTabsEnum::SERVICES->value => $this->tab == PalletReturnTabsEnum::SERVICES->value ?
                    fn () => FulfilmentTransactionResource::collection(IndexServiceInPalletReturn::run($palletReturn))
                    : Inertia::lazy(fn () => FulfilmentTransactionResource::collection(IndexServiceInPalletReturn::run($palletReturn))),

                PalletReturnTabsEnum::PHYSICAL_GOODS->value => $this->tab == PalletReturnTabsEnum::PHYSICAL_GOODS->value ?
                    fn () => FulfilmentTransactionResource::collection(IndexPhysicalGoodInPalletReturn::run($palletReturn))
                    : Inertia::lazy(fn () => FulfilmentTransactionResource::collection(IndexPhysicalGoodInPalletReturn::run($palletReturn)))
            ]
        )->table(
            IndexPalletsInReturn::make()->tableStructure(
                $palletReturn,
                request: $request,
                prefix: PalletReturnTabsEnum::PALLETS->value
            )
        )->table(
            IndexStoredItemsInReturn::make()->tableStructure(
                $palletReturn,
                request: $request,
                prefix: PalletReturnTabsEnum::STORED_ITEMS->value
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
                            'label' => __('pallet returns')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $palletReturn->slug,
                        ],

                    ],
                    'suffix' => $suffix
                ],
            ];
        };

        $palletReturn = PalletReturn::where('slug', $routeParameters['palletReturn'])->first();

        return match ($routeName) {
            'retina.storage.pallet-returns.show' => array_merge(
                ShowStorageDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    $palletReturn,
                    [
                        'index' => [
                            'name'       => 'retina.storage.pallet-returns.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'retina.storage.pallet-returns.show',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                )
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
                'label' => $palletReturn->slug,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation'   => $palletReturn->organisation->slug,
                        'warehouse'      => $palletReturn->warehouse->slug,
                        'palletReturn'   => $palletReturn->slug
                    ]

                ]
            ],
            'FulfilmentCustomer' => [
                'label' => $palletReturn->slug,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation'       => $palletReturn->organisation->slug,
                        'fulfilment'         => $palletReturn->fulfilment->slug,
                        'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->slug,
                        'palletReturn'       => $palletReturn->slug
                    ]

                ]
            ],
            default => []
        };
    }
}
