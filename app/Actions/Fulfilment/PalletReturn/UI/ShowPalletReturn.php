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
use App\Actions\Fulfilment\StoredItem\UI\IndexStoredItemsInReturn;
use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasFulfilmentAssetsAuthorisation;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Enums\Fulfilment\PalletReturn\PalletReturnTypeEnum;
use App\Enums\UI\Fulfilment\PalletReturnTabsEnum;
use App\Http\Resources\Fulfilment\FulfilmentCustomerResource;
use App\Http\Resources\Fulfilment\PalletReturnItemsResource;
use App\Http\Resources\Fulfilment\PalletReturnResource;
use App\Http\Resources\Fulfilment\PalletReturnsResource;
use App\Http\Resources\Helpers\AddressResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletReturn;
use App\Actions\Helpers\Country\UI\GetAddressData;
use App\Http\Resources\Fulfilment\FulfilmentTransactionsResource;
use App\Http\Resources\Fulfilment\PalletReturnStoredItemsResource;
use App\Http\Resources\Helpers\CurrencyResource;
use App\Models\Helpers\Address;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
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

    /** @noinspection PhpUnusedParameterInspection */
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
        //todo this should be $palletReturn->type
        //$type='StoredItem';
        $actions = [];


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




        if($this->canEdit) {
            $actions = $palletReturn->state == PalletReturnStateEnum::IN_PROCESS ? [
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
                                'name'       => 'grp.models.pallet-return.pallet.store',
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
                            // 'tooltip' => __('Add single service'),
                            // 'route'   => [
                            //     'name'       => 'grp.models.pallet-return.transaction.store',
                            //     'parameters' => [
                            //         'palletReturn' => $palletReturn->id
                            //     ]
                            // ]
                        ],
                        [
                            'type'    => 'button',
                            'style'   => 'secondary',
                            'icon'    => 'fal fa-plus',
                            'label'   => __('add service'),
                            'tooltip' => __('Add single service'),
                            'route'   => [
                                'name'       => 'grp.models.pallet-return.transaction.store',
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
                                'name'       => 'grp.models.pallet-return.transaction.store',
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
                        'name'       => 'grp.models.fulfilment-customer.pallet-return.submit_and_confirm',
                        'parameters' => [
                            'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->id,
                            'palletReturn'       => $palletReturn->id
                        ]
                        ],
                    'disabled' => $palletReturn->delivery_address_id === null && $palletReturn->collection_address_id === null
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



        $addresses = $palletReturn->fulfilmentCustomer->customer->addresses;

        $processedAddresses = $addresses->map(function ($address) {


            if(!DB::table('model_has_addresses')->where('address_id', $address->id)->where('model_type', '=', 'Customer')->exists()) {

                return $address->setAttribute('can_delete', false)
                    ->setAttribute('can_edit', true);
            }


            return $address->setAttribute('can_delete', true)
                            ->setAttribute('can_edit', true);
        });

        $customerAddressId              = $palletReturn->fulfilmentCustomer->customer->address->id;
        $customerDeliveryAddressId      = $palletReturn->fulfilmentCustomer->customer->deliveryAddress->id;
        $palletReturnDeliveryAddressIds = PalletReturn::where('fulfilment_customer_id', $palletReturn->fulfilment_customer_id)
                                            ->pluck('delivery_address_id')
                                            ->unique()
                                            ->toArray();

        $forbiddenAddressIds = array_merge(
            $palletReturnDeliveryAddressIds,
            [$customerAddressId, $customerDeliveryAddressId]
        );

        $processedAddresses->each(function ($address) use ($forbiddenAddressIds) {
            if (in_array($address->id, $forbiddenAddressIds, true)) {
                $address->setAttribute('can_delete', false)
                        ->setAttribute('can_edit', true);
            }
        });

        $addressCollection = AddressResource::collection($processedAddresses);
        // dd($palletReturn->fulfilmentCustomer->customer->address_id);
        // dd($addressCollection);
        // dd($palletReturnDeliveryAddressIds);

        if($palletReturn->type==PalletReturnTypeEnum::STORED_ITEM) {
            $afterTitle=[
                'label'=> '('.__('Stored items').')'
                ];
        } else {
            $afterTitle=[
                'label'=> '('.__('Whole pallets').')'
            ];
        }

        $showGrossAndDiscount = $palletReturn->gross_amount !== $palletReturn->net_amount;

        // dd($palletReturn->deliveryAddress);
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
                    'afterTitle'=> $afterTitle,
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

                'interest'  => [
                    'pallets_storage' => $palletReturn->fulfilmentCustomer->pallets_storage,
                    'items_storage'   => $palletReturn->fulfilmentCustomer->items_storage,
                    'dropshipping'    => $palletReturn->fulfilmentCustomer->dropshipping,
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

                'upload_spreadsheet' => [
                    'event'             => 'action-progress',
                    'channel'           => 'grp.personal.' . $this->organisation->id,
                    'required_fields'   => ['pallet_stored_item', 'pallet', 'stored_item', 'quantity'],
                    'template'          => [
                        'label' => 'Download template (.xlsx)',
                    ],
                    'route' => [
                        'upload'  => [
                            'name'       => 'grp.models.pallet-return.stored-item.upload',
                            'parameters' => [
                                'palletReturn' => $palletReturn->id
                            ]
                        ],
                        'history' => [
                            'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet_returns.pallets.uploads.history',
                            'parameters' => [
                                'organisation'       => $palletReturn->organisation->slug,
                                'fulfilment'         => $palletReturn->fulfilment->slug,
                                'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->slug,
                                'palletReturn'       => $palletReturn->slug
                            ]
                        ],
                        'download' => [
                            'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet_returns.pallets.stored-items.export',
                            'parameters' => [
                                'organisation'       => $palletReturn->organisation->slug,
                                'fulfilment'         => $palletReturn->fulfilment->slug,
                                'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->slug,
                                'type'               => 'xlsx'
                            ]
                        ],
                    ]
                    // 'templates' => [
                    //     'label' => 'Download Pallets & Stored Items (.xlsx)',
                    //     'route' => [
                    //         'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet_returns.pallets.stored-items.export',
                    //         'parameters' => $request->route()->originalParameters()
                    //     ]
                    // ]
                ],

                'palletRoute' => [
                    'index' => [
                        'name'       => 'grp.json.fulfilment.return.pallets',
                        'parameters' => [
                            'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->slug
                        ]
                    ],
                    'store' => [
                        'name'       => 'grp.models.pallet-return.pallet.store',
                        'parameters' => [
                            'palletReturn'       => $palletReturn->id
                        ]
                    ]
                ],
                'storedItemRoute' => [
                    'index' => [
                        'name'       => 'grp.json.fulfilment.return.stored-items',
                        'parameters' => [
                            'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->slug,
                            'palletReturn'       => $palletReturn->slug
                        ]
                    ],
                    'store' => [
                        'name'       => 'grp.models.pallet-return.stored_item.store',
                        'parameters' => [
                            'palletReturn'       => $palletReturn->id
                        ]
                    ]
                ],

                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => $navigation
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
                                ],
                                'address_list'                   => $addressCollection,
                                'pinned_address_id'              => $palletReturn->fulfilmentCustomer->customer->delivery_address_id,
                                'home_address_id'                => $palletReturn->fulfilmentCustomer->customer->address_id,
                                'current_selected_address_id'    => $palletReturn->delivery_address_id,
                                'selected_delivery_addresses_id' => $palletReturnDeliveryAddressIds,
                                'routes_list'                    => [
                                    'pinned_route'                   => [
                                        'method'     => 'patch',
                                        'name'       => 'grp.models.customer.delivery-address.update',
                                        'parameters' => [
                                            'customer' => $palletReturn->fulfilmentCustomer->customer_id
                                        ]
                                    ],
                                    'delete_route'  => [
                                        'method'     => 'delete',
                                        'name'       => 'grp.models.fulfilment-customer.delivery-address.delete',
                                        'parameters' => [
                                            'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->id
                                        ]
                                    ],
                                    'store_route' => [
                                        'method'      => 'post',
                                        'name'        => 'grp.models.fulfilment-customer.address.store',
                                        'parameters'  => [
                                            'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->id
                                        ]
                                    ],
                                ]
                            ],
                        ]
                    ),
                    'delivery_status'              => PalletReturnStateEnum::stateIcon()[$palletReturn->state->value],
                    'order_summary'                => [
                        [

                            // [
                            //     'label'         => __('Pallets'),
                            //     'quantity'      => $palletReturn->stats->number_pallets ?? 0,
                            //     'price_base'    => '',
                            //     'price_total'   => ''
                            // ],
                            [
                                'label'         => __('Services'),
                                'quantity'      => $palletReturn->stats->number_services ?? 0,
                                'price_base'    => '',
                                'price_total'   => $palletReturn->services_amount
                            ],
                            [
                                'label'         => __('Physical Goods'),
                                'quantity'      => $palletReturn->stats->number_physical_goods ?? 0,
                                'price_base'    => '',
                                'price_total'   => $palletReturn->goods_amount
                            ],

                        ],
                        $showGrossAndDiscount ? [
                            [
                                'label'         => __('Gross'),
                                'information'   => '',
                                'price_total'   => $palletReturn->gross_amount
                            ],
                            [
                                'label'         => __('Discounts'),
                                'information'   => '',
                                'price_total'   => $palletReturn->discount_amount
                            ],
                        ] : [],
                        $showGrossAndDiscount ? [
                            [
                                'label'         => __('Net'),
                                'information'   => '',
                                'price_total'   => $palletReturn->net_amount
                            ],
                            [
                                'label'         => __('Tax').' '.$palletReturn->taxCategory->rate * 100 . '%',
                                'information'   => '',
                                'price_total'   => $palletReturn->tax_amount
                            ],
                        ] : [
                            [
                                'label'         => __('Net'),
                                'information'   => '',
                                'price_total'   => $palletReturn->net_amount
                            ],
                            [
                                'label'         => __('Tax').' '.$palletReturn->taxCategory->rate * 100 . '%',
                                'information'   => '',
                                'price_total'   => $palletReturn->tax_amount
                            ],
                        ],
                        [
                            [
                                'label'         => __('Total'),
                                'price_total'   => $palletReturn->total_amount
                            ],
                        ],
                        'currency'                => CurrencyResource::make($palletReturn->currency),
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
                    'name'       => 'grp.json.fulfilment.return.services.index',
                    'parameters' => [
                        'fulfilment'     => $palletReturn->fulfilment->slug,
                        'scope'          => $palletReturn->slug
                    ]
                ],
                'physical_good_list_route'   => [
                    'name'       => 'grp.json.fulfilment.return.physical-goods.index',
                    'parameters' => [
                        'fulfilment'     => $palletReturn->fulfilment->slug,
                        'scope'          => $palletReturn->slug
                    ]
                ],

                PalletReturnTabsEnum::PALLETS->value => $this->tab == PalletReturnTabsEnum::PALLETS->value ?
                    fn () => PalletReturnItemsResource::collection(IndexPalletsInReturn::run($palletReturn))
                    : Inertia::lazy(fn () => PalletReturnItemsResource::collection(IndexPalletsInReturn::run($palletReturn))),

                PalletReturnTabsEnum::STORED_ITEMS->value => $this->tab == PalletReturnTabsEnum::STORED_ITEMS->value ?
                    fn () => PalletReturnStoredItemsResource::collection(IndexStoredItemsInReturn::run($palletReturn->fulfilmentCustomer)) //todo idk if this is right
                    : Inertia::lazy(fn () => PalletReturnStoredItemsResource::collection(IndexStoredItemsInReturn::run($palletReturn->fulfilmentCustomer))), //todo idk if this is right

                PalletReturnTabsEnum::SERVICES->value => $this->tab == PalletReturnTabsEnum::SERVICES->value ?
                    fn () => FulfilmentTransactionsResource::collection(IndexServiceInPalletReturn::run($palletReturn))
                    : Inertia::lazy(fn () => FulfilmentTransactionsResource::collection(IndexServiceInPalletReturn::run($palletReturn))),

                PalletReturnTabsEnum::PHYSICAL_GOODS->value => $this->tab == PalletReturnTabsEnum::PHYSICAL_GOODS->value ?
                    fn () => FulfilmentTransactionsResource::collection(IndexPhysicalGoodInPalletReturn::run($palletReturn))
                    : Inertia::lazy(fn () => FulfilmentTransactionsResource::collection(IndexPhysicalGoodInPalletReturn::run($palletReturn))),
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
                prefix: PalletReturnTabsEnum::STORED_ITEMS->value,
                modelOperations: [
                    'createLink' => [
                        [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('Save'),
                            'label'   => __('Save'),
                            'route'   => [
                                'method'     => 'post',
                                'name'       => 'grp.org.shops.show.crm.customers.show.web-users.create',
                                'parameters' => [
                                    $palletReturn->organisation->slug,
                                    $palletReturn->fulfilment->shop->slug,
                                    $palletReturn->slug
                                ]
                            ]
                        ]
                    ]
                ],
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
