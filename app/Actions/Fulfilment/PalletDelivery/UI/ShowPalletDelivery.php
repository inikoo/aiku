<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 17:41:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery\UI;

use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\Pallet\UI\IndexPalletsInDelivery;
use App\Actions\Fulfilment\UI\Catalogue\Rentals\IndexFulfilmentRentals;
use App\Actions\Fulfilment\WithFulfilmentCustomerSubNavigation;
use App\Actions\Helpers\Media\UI\IndexAttachments;
use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasFulfilmentAssetsAuthorisation;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletTypeEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Enums\Fulfilment\RecurringBill\RecurringBillStatusEnum;
use App\Enums\UI\Fulfilment\PalletDeliveryTabsEnum;
use App\Http\Resources\Fulfilment\FulfilmentCustomerResource;
use App\Http\Resources\Fulfilment\FulfilmentTransactionsResource;
use App\Http\Resources\Fulfilment\PalletDeliveryResource;
use App\Http\Resources\Fulfilment\PalletsResource;
use App\Http\Resources\Fulfilment\RentalsResource;
use App\Http\Resources\Helpers\Attachment\AttachmentsResource;
use App\Http\Resources\Helpers\CurrencyResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowPalletDelivery extends OrgAction
{
    use HasFulfilmentAssetsAuthorisation;
    use WithFulfilmentCustomerSubNavigation;

    private Warehouse|FulfilmentCustomer|Fulfilment $parent;

    public function handle(PalletDelivery $palletDelivery): PalletDelivery
    {
        return $palletDelivery;
    }


    public function asController(
        Organisation $organisation,
        Fulfilment $fulfilment,
        PalletDelivery $palletDelivery,
        ActionRequest $request
    ): PalletDelivery {
        $this->parent = $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(PalletDeliveryTabsEnum::values());

        return $this->handle($palletDelivery);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouse(
        Organisation $organisation,
        Warehouse $warehouse,
        PalletDelivery $palletDelivery,
        ActionRequest $request
    ): PalletDelivery {
        $this->parent = $warehouse;
        $this->initialisationFromWarehouse($warehouse, $request)->withTab(PalletDeliveryTabsEnum::values());

        return $this->handle($palletDelivery);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilmentCustomer(
        Organisation $organisation,
        Fulfilment $fulfilment,
        FulfilmentCustomer $fulfilmentCustomer,
        PalletDelivery $palletDelivery,
        ActionRequest $request
    ): PalletDelivery {
        $this->parent = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(PalletDeliveryTabsEnum::values());


        return $this->handle($palletDelivery);
    }

    public function htmlResponse(PalletDelivery $palletDelivery, ActionRequest $request): Response
    {
        $subNavigation = [];
        if ($this->parent instanceof FulfilmentCustomer) {
            $subNavigation = $this->getFulfilmentCustomerSubNavigation($this->parent, $request);
        }

        $numberPalletsStateBookingIn = $palletDelivery->pallets()->where('state', PalletStateEnum::BOOKING_IN)->count();
        $numberPalletsRentalNotSet   = $palletDelivery->pallets()->whereNull('rental_id')->count();

        $numberPallets       = $palletDelivery->fulfilmentCustomer->pallets()->count();
        $numberStoredPallets = $palletDelivery->pallets()->where('state', PalletDeliveryStateEnum::BOOKED_IN->value)->count();

        $totalPallets = $numberPallets + $numberStoredPallets;
        $pdfButton    = [
            'type'   => 'button',
            'style'  => 'tertiary',
            'label'  => 'PDF',
            'target' => '_blank',
            'icon'   => 'fal fa-file-pdf',
            'key'    => 'action',
            'route'  => [
                'name'       => 'grp.models.pallet-delivery.pdf',
                'parameters' => [
                    'palletDelivery' => $palletDelivery->id
                ]
            ]
        ];

        $actions = [];
        if ($this->canEdit) {
            $actions = match ($palletDelivery->state) {
                PalletDeliveryStateEnum::IN_PROCESS => [
                    [
                        'type'   => 'buttonGroup',
                        'key'    => 'upload-add',
                        'button' => [
                            [
                                'type'    => 'button',
                                'style'   => 'secondary',
                                'icon'    => ['fal', 'fa-upload'],
                                'label'   => '',
                                'key'     => 'upload',
                                'tooltip' => __('Upload pallets via spreadsheet'),
                            ],
                            [
                                'type'  => 'button',
                                'style' => 'secondary',
                                'icon'  => ['far', 'fa-layer-plus'],
                                'label' => 'multiple',
                                'key'   => 'multiple',
                                'route' => [
                                    'name'       => 'grp.models.pallet-delivery.multiple-pallets.store',
                                    'parameters' => [
                                        'palletDelivery' => $palletDelivery->id
                                    ]
                                ]
                            ],
                            [
                                'type'    => 'button',
                                'style'   => 'secondary',
                                'icon'    => 'fal fa-plus',
                                'label'   => __('add pallet'),
                                'key'     => 'add-pallet',
                                'tooltip' => __('Add single pallet'),
                                'route'   => [
                                    'name'       => 'grp.models.pallet-delivery.pallet.store',
                                    'parameters' => [
                                        'palletDelivery' => $palletDelivery->id
                                    ]
                                ]
                            ],
                            [
                                'type'    => 'button',
                                'style'   => 'secondary',
                                'icon'    => 'fal fa-plus',
                                'key'     => 'add-service',
                                'label'   => __('add service'),
                                'tooltip' => __('Add single service'),
                                'route'   => [
                                    'name'       => 'grp.models.pallet-delivery.transaction.store',
                                    'parameters' => [
                                        'palletDelivery' => $palletDelivery->id
                                    ]
                                ]
                            ],
                            [
                                'type'    => 'button',
                                'style'   => 'secondary',
                                'icon'    => 'fal fa-plus',
                                'key'     => 'add_physical_good',
                                'label'   => __('add physical good'),
                                'tooltip' => __('Add physical good'),
                                'route'   => [
                                    'name'       => 'grp.models.pallet-delivery.transaction.store',
                                    'parameters' => [
                                        'palletDelivery' => $palletDelivery->id
                                    ]
                                ]
                            ]
                        ]
                    ],
                    ($palletDelivery->pallets()->count() > 0) ?
                        [
                            'type'    => 'button',
                            'style'   => 'save',
                            'tooltip' => __('submit'),
                            'label'   => __('submit'),
                            'key'     => 'action',
                            'route'   => [
                                'method'     => 'post',
                                'name'       => 'grp.models.pallet-delivery.submit_and_confirm',
                                'parameters' => [
                                    'palletDelivery' => $palletDelivery->id
                                ]
                            ]
                        ] : [],
                ],
                PalletDeliveryStateEnum::SUBMITTED => [
                    [
                        'type'   => 'buttonGroup',
                        'key'    => 'upload-add',
                        'button' => [
                            [
                                'type'    => 'button',
                                'style'   => 'secondary',
                                'icon'    => 'fal fa-plus',
                                'key'     => 'add-service',
                                'label'   => __('add service'),
                                'tooltip' => __('Add single service'),
                                'route'   => [
                                    'name'       => 'grp.models.pallet-delivery.transaction.store',
                                    'parameters' => [
                                        'palletDelivery' => $palletDelivery->id
                                    ]
                                ]
                            ],
                            [
                                'type'    => 'button',
                                'style'   => 'secondary',
                                'icon'    => 'fal fa-plus',
                                'key'     => 'add_physical_good',
                                'label'   => __('add physical good'),
                                'tooltip' => __('Add physical good'),
                                'route'   => [
                                    'name'       => 'grp.models.pallet-delivery.transaction.store',
                                    'parameters' => [
                                        'palletDelivery' => $palletDelivery->id
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type'    => 'button',
                        'style'   => 'save',
                        'tooltip' => __('confirm'),
                        'label'   => __('confirm'),
                        'key'     => 'action',
                        'route'   => [
                            'method'     => 'post',
                            'name'       => 'grp.models.pallet-delivery.confirm',
                            'parameters' => [
                                'palletDelivery' => $palletDelivery->id
                            ]
                        ]
                    ]
                ],
                PalletDeliveryStateEnum::CONFIRMED => [
                    [
                        'type'   => 'buttonGroup',
                        'key'    => 'upload-add',
                        'button' => [
                            [
                                'type'    => 'button',
                                'style'   => 'secondary',
                                'icon'    => 'fal fa-plus',
                                'key'     => 'add-service',
                                'label'   => __('add service'),
                                'tooltip' => __('Add single service'),
                                'route'   => [
                                    'name'       => 'grp.models.pallet-delivery.transaction.store',
                                    'parameters' => [
                                        'palletDelivery' => $palletDelivery->id
                                    ]
                                ]
                            ],
                            [
                                'type'    => 'button',
                                'style'   => 'secondary',
                                'icon'    => 'fal fa-plus',
                                'key'     => 'add_physical_good',
                                'label'   => __('add physical good'),
                                'tooltip' => __('Add physical good'),
                                'route'   => [
                                    'name'       => 'grp.models.pallet-delivery.transaction.store',
                                    'parameters' => [
                                        'palletDelivery' => $palletDelivery->id
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type'    => 'button',
                        'style'   => 'negative',
                        'icon'    => 'fal fa-times',
                        'tooltip' => __('Cancel the delivery'),
                        'label'   => __('cancel'),
                        'key'     => 'action',
                        'route'   => [
                            'method'     => 'post',
                            'name'       => 'grp.models.pallet-delivery.cancel',
                            'parameters' => [
                                'palletDelivery' => $palletDelivery->id
                            ]
                        ]
                    ],
                    [
                        'type'    => 'button',
                        'style'   => 'primary',
                        'icon'    => 'fal fa-check',
                        'tooltip' => __('Mark as received'),
                        'label'   => __('receive'),
                        'key'     => 'action',
                        'route'   => [
                            'method'     => 'post',
                            'name'       => 'grp.models.pallet-delivery.received',
                            'parameters' => [
                                'palletDelivery' => $palletDelivery->id
                            ]
                        ]
                    ]
                ],
                PalletDeliveryStateEnum::RECEIVED => [
                    [
                        'type'    => 'button',
                        'style'   => 'edit',
                        'tooltip' => __('Edit'),
                        'key'     => 'action',
                        'route'   => [
                            'method'     => 'get',
                            'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet_deliveries.edit',
                            'parameters' => [
                                'organisation'       => $palletDelivery->organisation->slug,
                                'fulfilment'         => $palletDelivery->fulfilment->slug,
                                'fulfilmentCustomer' => $palletDelivery->fulfilmentCustomer->slug,
                                'palletDelivery'     => $palletDelivery->slug
                            ]
                        ]
                    ],
                    [
                        'type'   => 'buttonGroup',
                        'key'    => 'upload-add',
                        'button' => [
                            [
                                'type'    => 'button',
                                'style'   => 'secondary',
                                'icon'    => 'fal fa-plus',
                                'key'     => 'add-service',
                                'label'   => __('add service'),
                                'tooltip' => __('Add single service'),
                                'route'   => [
                                    'name'       => 'grp.models.pallet-delivery.transaction.store',
                                    'parameters' => [
                                        'palletDelivery' => $palletDelivery->id
                                    ]
                                ]
                            ],
                            [
                                'type'    => 'button',
                                'style'   => 'secondary',
                                'icon'    => 'fal fa-plus',
                                'key'     => 'add_physical_good',
                                'label'   => __('add physical good'),
                                'tooltip' => __('Add physical good'),
                                'route'   => [
                                    'name'       => 'grp.models.pallet-delivery.transaction.store',
                                    'parameters' => [
                                        'palletDelivery' => $palletDelivery->id
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'type'    => 'button',
                        'style'   => 'tertiary',
                        'icon'    => 'fal fa-undo-alt',
                        'tooltip' => __('Revert to Confirmed'),
                        'label'   => __('Revert to Confirmed'),
                        'key'     => 'revert',
                        'route'   => [
                            'method'     => 'post',
                            'name'       => 'grp.models.pallet-delivery.confirm',
                            'parameters' => [
                                'palletDelivery' => $palletDelivery->id
                            ]
                        ]
                    ],
                    [
                        'type'    => 'button',
                        'style'   => 'primary',
                        'icon'    => 'fal fa-clipboard',
                        'tooltip' => __('Start booking'),
                        'label'   => __('start booking'),
                        'key'     => 'action',
                        'route'   => [
                            'method'     => 'post',
                            'name'       => 'grp.models.pallet-delivery.booking',
                            'parameters' => [
                                'palletDelivery' => $palletDelivery->id
                            ]
                        ]
                    ],
                ],
                PalletDeliveryStateEnum::BOOKING_IN => [
                    [
                        'type'   => 'buttonGroup',
                        'key'    => 'upload-add',
                        'button' => [
                            [
                                'type'    => 'button',
                                'style'   => 'secondary',
                                'icon'    => 'fal fa-plus',
                                'key'     => 'add-service',
                                'label'   => __('add service'),
                                'tooltip' => __('Add single service'),
                                'route'   => [
                                    'name'       => 'grp.models.pallet-delivery.transaction.store',
                                    'parameters' => [
                                        'palletDelivery' => $palletDelivery->id
                                    ]
                                ]
                            ],
                            [
                                'type'    => 'button',
                                'style'   => 'secondary',
                                'icon'    => 'fal fa-plus',
                                'key'     => 'add_physical_good',
                                'label'   => __('add physical good'),
                                'tooltip' => __('Add physical good'),
                                'route'   => [
                                    'name'       => 'grp.models.pallet-delivery.transaction.store',
                                    'parameters' => [
                                        'palletDelivery' => $palletDelivery->id
                                    ]
                                ]
                            ]
                        ]
                    ],
                    ($numberPalletsStateBookingIn == 0 and $numberPalletsRentalNotSet == 0) ? [
                        'type'    => 'button',
                        'style'   => 'primary',
                        'icon'    => 'fal fa-check',
                        'tooltip' => __('Confirm booking'),
                        'label'   => __('Finish booking'),
                        'key'     => 'action',
                        'route'   => [
                            'method'     => 'post',
                            'name'       => 'grp.models.pallet-delivery.booked-in',
                            'parameters' => [
                                'palletDelivery' => $palletDelivery->id
                            ]
                        ]
                    ] : null,
                ],
                PalletDeliveryStateEnum::BOOKED_IN => [
                    $palletDelivery->recurringBill->status == RecurringBillStatusEnum::CURRENT ? [
                        'type'   => 'buttonGroup',
                        'key'    => 'upload-add',
                        'button' => [
                                [
                                    'type'    => 'button',
                                    'style'   => 'secondary',
                                    'icon'    => 'fal fa-plus',
                                    'key'     => 'add-service',
                                    'label'   => __('add service'),
                                    'tooltip' => __('Add single service'),
                                    'route'   => [
                                        'name'       => 'grp.models.pallet-delivery.transaction.store',
                                        'parameters' => [
                                            'palletDelivery' => $palletDelivery->id
                                        ]
                                    ]
                                ],
                                [
                                    'type'    => 'button',
                                    'style'   => 'secondary',
                                    'icon'    => 'fal fa-plus',
                                    'key'     => 'add_physical_good',
                                    'label'   => __('add physical good'),
                                    'tooltip' => __('Add physical good'),
                                    'route'   => [
                                        'name'       => 'grp.models.pallet-delivery.transaction.store',
                                        'parameters' => [
                                            'palletDelivery' => $palletDelivery->id
                                        ]
                                    ]
                                ]
                        ]
                    ] : []
                ],
                default => []
            };

            if (!in_array($palletDelivery->state, [
                PalletDeliveryStateEnum::IN_PROCESS,
                PalletDeliveryStateEnum::SUBMITTED
            ])) {
                $actions = array_merge($actions, [$pdfButton]);
            } else {
                $actions = array_merge([
                    [
                        'type'    => 'button',
                        'style'   => 'delete',
                        'tooltip' => __('delete'),
                        'label'   => __('delete'),
                        'key'     => 'delete_delivery',
                        'ask_why' => true,
                        'route'   => [
                            'method'     => 'patch',
                            'name'       => 'grp.models.pallet-delivery.delete',
                            'parameters' => [
                                'palletDelivery' => $palletDelivery->id
                            ]
                        ]
                    ]
                ], $actions);
            }
        }

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
                            'message' => __("Pallet almost reached the limit: :palletLimitLeft left", ['palletLimitLeft' => $palletLimitLeft])

                        ]
                        : null)));

        $rentalList = [];

        if (in_array($palletDelivery->state, [PalletDeliveryStateEnum::BOOKING_IN, PalletDeliveryStateEnum::BOOKED_IN])) {
            $rentalList = RentalsResource::collection(IndexFulfilmentRentals::run($palletDelivery->fulfilment, 'rentals'))->toArray($request);
        }

        $palletPriceTotal = 0;
        foreach ($palletDelivery->pallets as $pallet) {
            $discount         = $pallet->rentalAgreementClause ? $pallet->rentalAgreementClause->percentage_off / 100 : null;
            $rentalPrice      = $pallet->rental->price ?? 0;
            $palletPriceTotal += $rentalPrice - $rentalPrice * $discount;
        }

        $showGrossAndDiscount = $palletDelivery->gross_amount !== $palletDelivery->net_amount;

        $recurringBillData = null;
        if ($palletDelivery->recurringBill) {
            $recurringBill      = $palletDelivery->recurringBill;
            $recurringBillRoute = null;
            if ($this->parent instanceof Fulfilment) {
                $recurringBillRoute = [
                    'name'       => 'grp.org.fulfilments.show.operations.recurring_bills.current.show',
                    'parameters' => [
                        'organisation'  => $recurringBill->organisation->slug,
                        'fulfilment'    => $this->parent->slug,
                        'recurringBill' => $recurringBill->slug
                    ]
                ];
            } elseif ($this->parent instanceof FulfilmentCustomer) {
                $recurringBillRoute = [
                    'name'       => 'grp.org.fulfilments.show.crm.customers.show.recurring_bills.show',
                    'parameters' => [
                        'organisation'       => $recurringBill->organisation->slug,
                        'fulfilment'         => $this->parent->fulfilment->slug,
                        'fulfilmentCustomer' => $this->parent->slug,
                        'recurringBill'      => $recurringBill->slug
                    ]
                ];
            }
            if ($recurringBillRoute) {
                $recurringBillData = [
                    'reference'    => $recurringBill->reference,
                    'status'       => $recurringBill->status,
                    'total_amount' => $recurringBill->total_amount,
                    'route'        => $recurringBillRoute
                ];
            }
        }

        return Inertia::render(
            'Org/Fulfilment/PalletDelivery',
            [
                'title'       => __('pallet delivery'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'  => [
                    'previous' => $this->getPrevious($palletDelivery, $request),
                    'next'     => $this->getNext($palletDelivery, $request),
                ],
                'pageHead'    => [
                    // 'container' => $container,
                    'title'         => $palletDelivery->reference,
                    'icon'          => [
                        'icon'  => ['fal', 'fa-truck-couch'],
                        'title' => $palletDelivery->reference
                    ],
                    'subNavigation' => $subNavigation,
                    'model'         => __('pallet delivery'),
                    'iconRight'     => $palletDelivery->state->stateIcon()[$palletDelivery->state->value],
                    'edit'          => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                            'parameters' => array_values($request->route()->originalParameters())
                        ]
                    ] : false,
                    'actions'       => $actions,
                ],

                'can_edit_transactions' => true,

                'interest' => [
                    'pallets_storage' => $palletDelivery->fulfilmentCustomer->pallets_storage,
                    'items_storage'   => $palletDelivery->fulfilmentCustomer->items_storage,
                    'dropshipping'    => $palletDelivery->fulfilmentCustomer->dropshipping,
                ],

                'updateRoute' => [
                    'name'       => 'grp.models.pallet-delivery.update',
                    'parameters' => [
                        'palletDelivery' => $palletDelivery->id
                    ]
                ],

                'deleteServiceRoute' => [
                    'name'       => 'org.models.pallet-delivery.service.delete',
                    'parameters' => [
                        'palletDelivery' => $palletDelivery->id
                    ]
                ],

                'deletePhysicalGoodRoute' => [
                    'name'       => 'org.models.pallet-delivery.physical_good.delete',
                    'parameters' => [
                        'palletDelivery' => $palletDelivery->id
                    ]
                ],

                'upload_spreadsheet' => [
                    'event'           => 'action-progress',
                    'channel'         => 'grp.personal.'.$this->organisation->id,
                    'required_fields' => ['type', 'customer_reference', 'notes', 'stored_item_reference', 'quantity', 'stored_item_name'],
                    'template'        => [
                        'label' => 'Download template (.xlsx)',
                    ],
                    'route'           => [
                        'upload'   => [
                            'name'       => 'grp.models.pallet-delivery.pallet.upload.with-stored-items',
                            'parameters' => [
                                'palletDelivery' => $palletDelivery->id
                            ]
                        ],
                        'uploadWithStoredItems'   => [
                            'name'       => 'grp.models.pallet-delivery.pallet.upload.with-stored-items',
                            'parameters' => [
                                'palletDelivery' => $palletDelivery->id
                            ]
                        ],
                        'history'  => [
                            'name'       => 'grp.json.pallet_delivery.recent_uploads',
                            'parameters' => [
                                'palletDelivery' => $palletDelivery->id
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
                ],

                'attachmentRoutes' => [
                    'attachRoute' => [
                        'name'       => 'grp.models.pallet-delivery.attachment.attach',
                        'parameters' => [
                            'palletDelivery' => $palletDelivery->id,
                        ],
                        'method'     => 'post'
                    ],
                    'detachRoute' => [
                        'name'       => 'grp.models.pallet-delivery.attachment.detach',
                        'parameters' => [
                            'palletDelivery' => $palletDelivery->id,
                        ],
                        'method'     => 'delete'
                    ]
                ],

                // 'uploadRoutes' => [
                //     'upload'  => [
                //         'name'       => 'grp.models.pallet-delivery.pallet.upload',
                //         'parameters' => [
                //             'palletDelivery' => $palletDelivery->id
                //         ]
                //     ],
                //     'history' => [
                //         'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet_deliveries.pallets.uploads.history',
                //         'parameters' => [
                //             'organisation'       => $palletDelivery->organisation->slug,
                //             'fulfilment'         => $palletDelivery->fulfilment->slug,
                //             'fulfilmentCustomer' => $palletDelivery->fulfilmentCustomer->id,
                //             'palletDelivery'     => $palletDelivery->reference
                //         ]
                //     ],
                //     'download' => [
                //         'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet_deliveries.pallets.uploads.templates',
                //         'parameters' => [
                //             'organisation'       => $palletDelivery->organisation->slug,
                //             'fulfilment'         => $palletDelivery->fulfilment->slug,
                //             'fulfilmentCustomer' => $palletDelivery->fulfilmentCustomer->slug,
                //             'palletDelivery'     => $palletDelivery->reference
                //         ]
                //     ],
                // ],

                'locationRoute' => [
                    'name'       => 'grp.org.warehouses.show.infrastructure.locations.index',
                    'parameters' => [
                        'organisation' => $palletDelivery->organisation->slug,
                        'warehouse'    => $palletDelivery->warehouse->slug
                    ]
                ],

                'rentalRoute' => [
                    'name'       => 'grp.org.fulfilments.show.catalogue.rentals.index',
                    'parameters' => [
                        'organisation' => $palletDelivery->organisation->slug,
                        'fulfilment'   => $palletDelivery->fulfilment->slug
                    ]
                ],

                'storedItemsRoute' => [
                    'index'  => [
                        'name'       => 'grp.org.fulfilments.show.crm.customers.show.stored-items.index',
                        'parameters' => [
                            'organisation'       => $palletDelivery->organisation->slug,
                            'fulfilment'         => $palletDelivery->fulfilment->slug,
                            'fulfilmentCustomer' => $palletDelivery->fulfilmentCustomer->slug,
                            'palletDelivery'     => $palletDelivery->slug
                        ]
                    ],
                    'store'  => [
                        'name'       => 'grp.models.fulfilment-customer.stored-items.store',
                        'parameters' => [
                            'fulfilmentCustomer' => $palletDelivery->fulfilmentCustomer->id
                        ]
                    ],
                    'delete' => [
                        'name' => 'grp.models.stored-items.delete'
                    ]
                ],

                'rental_lists'             => $rentalList,
                'service_list_route'       => [
                    'name'       => 'grp.json.fulfilment.delivery.services.index',
                    'parameters' => [
                        'fulfilment' => $palletDelivery->fulfilment->slug,
                        'scope'      => $palletDelivery->slug
                    ]
                ],
                'physical_good_list_route' => [
                    'name'       => 'grp.json.fulfilment.delivery.physical-goods.index',
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
                    'recurring_bill'      => $recurringBillData,
                    'order_summary'       => [
                        [
                            // [
                            //     'label'       => __('Pallets'),
                            //     'quantity'    => $palletDelivery->stats->number_pallets ?? 0,
                            //     'price_base'  => __('Multiple'),
                            //     'price_total' => $palletPriceTotal ?? 0
                            // ],
                            [
                                'label'       => __('Services'),
                                'quantity'    => $palletDelivery->stats->number_services ?? 0,
                                'price_base'  => __('Multiple'),
                                'price_total' => $palletDelivery->services_amount
                            ],
                            [
                                'label'       => __('Physical Goods'),
                                'quantity'    => $palletDelivery->stats->number_physical_goods ?? 0,
                                'price_base'  => __('Multiple'),
                                'price_total' => $palletDelivery->goods_amount
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
                        $showGrossAndDiscount
                            ? [
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
                        ]
                            : [
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
                'notes_data' => [
                    [
                        'label'    => __('Customer'),
                        'note'     => $palletDelivery->customer_notes ?? '',
                        'editable' => false,
                        'bgColor'  => 'blue',
                        'field'    => 'customer_notes'
                    ],
                    [
                        'label'    => __('Public'),
                        'note'     => $palletDelivery->public_notes ?? '',
                        'editable' => true,
                        'bgColor'  => 'pink',
                        'field'    => 'public_notes'
                    ],
                    [
                        'label'    => __('Private'),
                        'note'     => $palletDelivery->internal_notes ?? '',
                        'editable' => true,
                        'bgColor'  => 'purple',
                        'field'    => 'internal_notes'
                    ],
                ],

                'option_attach_file' => [
                    [
                        'name' => __('Other'),
                        'code' => 'Other'
                    ]
                ],

                'upload_pallet' => [
                    'title' => [
                        'label' => __('Upload pallet'),
                        'information' => __('The list of column file: customer_reference, notes')
                    ],
                    'progressDescription'   => __('Adding Pallet Deliveries'),
                    'preview_template'    => [
                        'unique_column' => [
                            'type'  => [
                                'label' => __('The valid type is ') . PalletTypeEnum::PALLET->value . ', ' . PalletTypeEnum::BOX->value . ', or ' . PalletTypeEnum::OVERSIZE->value . '. By default is ' . PalletTypeEnum::PALLET->value . '.'
                            ]
                        ],
                        'header' => ['type', 'customer_reference', 'notes'],
                        'rows' => [
                            [
                                'type' => 'Pallet',
                                'customer_reference' => 'PALLET1',
                                'notes' => 'notes',
                            ],
                        ]
                    ],
                    'upload_spreadsheet'    => [
                        'event'           => 'action-progress',
                        'channel'         => 'grp.personal.'.$this->organisation->id,
                        'required_fields' => ['customer_reference', 'notes', 'stored_items', 'type'],
                        'template'        => [
                            'label' => 'Download template (.xlsx)',
                        ],
                        'route'           => [
                            'upload'   => [
                                'name'       => 'grp.models.pallet-delivery.pallet.upload',
                                'parameters' => [
                                    'palletDelivery' => $palletDelivery->id
                                ]
                            ],
                            'history'  => [
                                'name'       => 'grp.json.pallet_delivery.recent_uploads',
                                'parameters' => [
                                    'palletDelivery' => $palletDelivery->id
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
                    ]
                ],
                'upload_stored_item' => [
                    'title' => [
                        'label' => __('Upload Customer\'s SKU'),
                        'information' => __('The list of column file: customer_reference, notes, stored_items')
                    ],
                    'progressDescription'   => __('Adding stored item'),
                    'preview_template'    => [
                        'unique_column' => [
                            'type'  => [
                                'label' => __('The valid type is ') . PalletTypeEnum::PALLET->value . ', ' . PalletTypeEnum::BOX->value . ', or ' . PalletTypeEnum::OVERSIZE->value . '. By default is ' . PalletTypeEnum::PALLET->value . '.'
                            ]
                        ],
                        'header' => ['type', 'customer_reference', 'notes', 'stored_item_reference', 'quantity', 'stored_item_name'],
                        'rows' => [
                            [
                                'type' => 'Pallet',
                                'customer_reference' => 'PALLET1',
                                'notes' => 'notes',
                                'stored_item_reference' => 'SKU1',
                                'quantity'  => 10,
                                'stored_item_name' => 'SKU 1'
                            ],
                        ]
                    ],
                    'upload_spreadsheet'    => [
                        'event'           => 'action-progress',
                        'channel'         => 'grp.personal.'.$this->organisation->id,
                        'required_fields' => ['type', 'customer_reference', 'notes', 'stored_item_reference', 'quantity', 'stored_item_name' ],
                        'template'        => [
                            'label' => 'Download template (.xlsx)',
                        ],
                        'route'           => [
                            'upload'   => [
                                'name'       => 'grp.models.pallet-delivery.pallet.upload.with-stored-items',
                                'parameters' => [
                                    'palletDelivery' => $palletDelivery->id
                                ]
                            ],
                            'history'  => [
                                'name'       => 'grp.json.pallet_delivery.recent_uploads',
                                'parameters' => [
                                    'palletDelivery' => $palletDelivery->id
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
                    ]
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

                PalletDeliveryTabsEnum::ATTACHMENTS->value => $this->tab == PalletDeliveryTabsEnum::ATTACHMENTS->value ?
                    fn () => AttachmentsResource::collection(IndexAttachments::run($palletDelivery, PalletDeliveryTabsEnum::ATTACHMENTS->value))
                    : Inertia::lazy(fn () => AttachmentsResource::collection(IndexAttachments::run($palletDelivery, PalletDeliveryTabsEnum::ATTACHMENTS->value))),

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
        )->table(IndexAttachments::make()->tableStructure(PalletDeliveryTabsEnum::ATTACHMENTS->value));
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

        $palletDelivery = PalletDelivery::where('slug', $routeParameters['palletDelivery'])->first();


        return match ($routeName) {
            'grp.org.fulfilments.show.operations.pallet-deliveries.show' =>
            array_merge(
                ShowFulfilment::make()->getBreadcrumbs(Arr::only($routeParameters, ['organisation', 'fulfilment'])),
                $headCrumb(
                    $palletDelivery,
                    [
                        'index' => [
                            'name'       => 'grp.org.fulfilments.show.operations.pallet-deliveries.index',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.fulfilments.show.operations.pallet-deliveries.show',
                            'parameters' => Arr::only(
                                $routeParameters,
                                ['organisation', 'fulfilment', 'palletDelivery']
                            )
                        ]
                    ],
                    $suffix
                )
            ),
            'grp.org.fulfilments.show.crm.customers.show.pallet_deliveries.show' =>
            array_merge(
                ShowFulfilmentCustomer::make()->getBreadcrumbs(
                    Arr::only($routeParameters, ['organisation', 'fulfilment', 'fulfilmentCustomer'])
                ),
                $headCrumb(
                    $palletDelivery,
                    [
                        'index' => [
                            'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet_deliveries.index',
                            'parameters' => Arr::only(
                                $routeParameters,
                                ['organisation', 'fulfilment', 'fulfilmentCustomer']
                            )
                        ],
                        'model' => [
                            'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet_deliveries.show',
                            'parameters' => Arr::only(
                                $routeParameters,
                                ['organisation', 'fulfilment', 'fulfilmentCustomer', 'palletDelivery']
                            )
                        ]
                    ],
                    $suffix
                )
            ),
            'grp.org.warehouses.show.incoming.pallet_deliveries.show' =>
            array_merge(
                ShowWarehouse::make()->getBreadcrumbs(
                    Arr::only($routeParameters, ['organisation', 'warehouse'])
                ),
                $headCrumb(
                    $palletDelivery,
                    [
                        'index' => [
                            'name'       => 'grp.org.warehouses.show.incoming.pallet_deliveries.index',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'warehouse'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.warehouses.show.incoming.pallet_deliveries.show',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'warehouse', 'palletDelivery'])
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
        if ($this->parent instanceof FulfilmentCustomer) {
            $previous = PalletDelivery::where('fulfilment_customer_id', $this->parent->id)->where('id', '<', $palletDelivery->id)->orderBy('id', 'desc')->first();
        } elseif ($this->parent instanceof Fulfilment) {
            $previous = PalletDelivery::where('fulfilment_id', $this->parent->id)->where('id', '<', $palletDelivery->id)->orderBy('id', 'desc')->first();
        } else {
            $previous = PalletDelivery::where('id', '<', $palletDelivery->id)->orderBy('id', 'desc')->first();
        }

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(PalletDelivery $palletDelivery, ActionRequest $request): ?array
    {
        if ($this->parent instanceof FulfilmentCustomer) {
            $next = PalletDelivery::where('fulfilment_customer_id', $this->parent->id)->where('id', '>', $palletDelivery->id)->orderBy('id')->first();
        } elseif ($this->parent instanceof Fulfilment) {
            $next = PalletDelivery::where('fulfilment_id', $this->parent->id)->where('id', '>', $palletDelivery->id)->orderBy('id')->first();
        } else {
            $next = PalletDelivery::where('id', '>', $palletDelivery->id)->orderBy('id')->first();
        }

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?PalletDelivery $palletDelivery, string $routeName): ?array
    {
        if (!$palletDelivery) {
            return null;
        }

        return match (class_basename($this->parent)) {
            'Warehouse' => [
                'label' => $palletDelivery->reference,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation'   => $palletDelivery->organisation->slug,
                        'warehouse'      => $palletDelivery->warehouse->slug,
                        'palletDelivery' => $palletDelivery->slug
                    ]

                ]
            ],
            'FulfilmentCustomer' => [
                'label' => $palletDelivery->reference,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation'       => $palletDelivery->organisation->slug,
                        'fulfilment'         => $palletDelivery->fulfilment->slug,
                        'fulfilmentCustomer' => $palletDelivery->fulfilmentCustomer->slug,
                        'palletDelivery'     => $palletDelivery->slug
                    ]

                ]
            ],
            default => []
        };
    }
}
