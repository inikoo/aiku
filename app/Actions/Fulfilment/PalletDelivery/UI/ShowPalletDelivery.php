<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 17:41:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery\UI;

use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\Pallet\UI\IndexPallets;
use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasFulfilmentAssetsAuthorisation;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Enums\UI\PalletDeliveryTabsEnum;
use App\Http\Resources\Fulfilment\FulfilmentCustomerResource;
use App\Http\Resources\Fulfilment\PalletDeliveryResource;
use App\Http\Resources\Fulfilment\PalletsResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowPalletDelivery extends OrgAction
{
    use HasFulfilmentAssetsAuthorisation;

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
        $container = null;
        if ($this->parent instanceof Warehouse) {
            $container = [
                'icon'    => ['fal', 'fa-warehouse'],
                'tooltip' => __('Warehouse'),
                'label'   => Str::possessive($this->parent->code)
            ];
        } elseif ($this->parent instanceof FulfilmentCustomer) {
            $container = [
                'icon'    => ['fal', 'fa-user'],
                'tooltip' => __('Customer'),
                'label'   => Str::possessive($this->parent->customer->reference)
            ];
        }
        $palletStateReceivedCount = $palletDelivery->pallets()->where('state', PalletStateEnum::RECEIVED)->count();

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
                                'label'   => 'upload',
                                'tooltip' => __('Upload pallet via file'),
                                'route'   => [
                                    'name'       => 'grp.models.pallet-delivery.pallet.import',
                                    'parameters' => [
                                        'palletDelivery' => $palletDelivery->id
                                    ]
                                ]
                            ],
                            [
                                'type'  => 'button',
                                'style' => 'secondary',
                                'icon'  => ['far', 'fa-layer-plus'],
                                'label' => 'multiple',
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
                                'tooltip' => __('Add single pallet'),
                                'route'   => [
                                    'name'       => 'grp.models.pallet-delivery.pallet.store',
                                    'parameters' => [
                                        'palletDelivery' => $palletDelivery->id
                                    ]
                                ]
                            ],
                        ]
                    ],
                    ($palletDelivery->pallets()->count() > 0) ?
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
                        ] : [],
                ],
                PalletDeliveryStateEnum::SUBMITTED => [
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
                    ],
                ],
                PalletDeliveryStateEnum::BOOKING_IN => [
                    $palletStateReceivedCount == 0 ? [
                        'type'    => 'button',
                        'style'   => 'primary',
                        'icon'    => 'fal fa-check',
                        'tooltip' => __('Confirm booking'),
                        'label'   => __('Save booking'),
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
                default => []
            };

            if (!in_array($palletDelivery->state, [
                PalletDeliveryStateEnum::IN_PROCESS,
                PalletDeliveryStateEnum::SUBMITTED
            ])) {
                $actions[] = [
                    'type'   => 'button',
                    'style'  => 'tertiary',
                    'label'  => 'PDF',
                    'target' => '_blank',
                    'icon'   => 'fal fa-file-pdf',
                    'key'    => 'action',
                    'label'  => 'PDF',
                    'route'  => [
                        'name'       => 'grp.models.pallet-delivery.pdf',
                        'parameters' => [
                            'palletDelivery' => $palletDelivery->id
                        ]
                    ]
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
                'navigation' => [
                    'previous' => $this->getPrevious($palletDelivery, $request),
                    'next'     => $this->getNext($palletDelivery, $request),
                ],
                'pageHead' => [
                    // 'container' => $container,
                    'title'     => $palletDelivery->reference,
                    'icon'      => [
                        'icon'  => ['fal', 'fa-truck-couch'],
                        'title' => $palletDelivery->reference
                    ],
                    'model'     => __('pallet delivery'),
                    'iconRight' => $palletDelivery->state->stateIcon()[$palletDelivery->state->value],
                    'edit'      => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                            'parameters' => array_values($request->route()->originalParameters())
                        ]
                    ] : false,


                    'actions' => $actions,


                ],

                'updateRoute' => [
                    'name'       => 'grp.models.pallet-delivery.update',
                    'parameters' => [
                        'palletDelivery'     => $palletDelivery->id
                    ]
                ],

                'upload' => [
                    'event'   => 'action-progress',
                    'channel' => 'grp.personal.' . $this->organisation->id
                ],

                'uploadRoutes' => [
                    'history' => [
                        'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet-deliveries.pallets.uploads.history',
                        'parameters' => [
                            'organisation'       => $palletDelivery->organisation->slug,
                            'fulfilment'         => $palletDelivery->fulfilment->slug,
                            'fulfilmentCustomer' => $palletDelivery->fulfilmentCustomer->id,
                            'palletDelivery'     => $palletDelivery->reference
                        ]
                    ],
                    'download' => [
                        'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet-deliveries.pallets.uploads.templates',
                        'parameters' => [
                            'organisation'       => $palletDelivery->organisation->slug,
                            'fulfilment'         => $palletDelivery->fulfilment->slug,
                            'fulfilmentCustomer' => $palletDelivery->fulfilmentCustomer->slug,
                            'palletDelivery'     => $palletDelivery->reference
                        ]
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
                        'name'       => 'grp.org.fulfilments.show.crm.customers.show.stored-items.index',
                        'parameters' => [
                            'organisation'       => $palletDelivery->organisation->slug,
                            'fulfilment'         => $palletDelivery->fulfilment->slug,
                            'fulfilmentCustomer' => $palletDelivery->fulfilmentCustomer->slug,
                            'palletDelivery'     => $palletDelivery->reference
                        ]
                    ],
                    'store' => [
                        'name'       => 'grp.models.fulfilment-customer.stored-items.store',
                        'parameters' => [
                            'fulfilmentCustomer' => $palletDelivery->fulfilmentCustomer->id
                        ]
                    ]
                ],

                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => PalletDeliveryTabsEnum::navigation()
                ],

                'data'             => PalletDeliveryResource::make($palletDelivery),
                'box_stats'        => [
                    'fulfilment_customer'          => FulfilmentCustomerResource::make($palletDelivery->fulfilmentCustomer)->getArray(),
                    'delivery_status'              => PalletDeliveryStateEnum::stateIcon()[$palletDelivery->state->value],
                ],
                'notes_data'             => [
                    [
                        'label'         => __('Customer'),
                        'note'          => $palletDelivery->customer_notes ?? '',
                        'editable'      => false,
                        'color'         => 'blue',
                        'field'         => 'customer_notes'
                    ],
                    [
                        'label'         => __('Public'),
                        'note'          => $palletDelivery->public_notes ?? '',
                        'editable'      => true,
                        'color'         => 'pink',
                        'field'         => 'public_notes'
                    ],
                    [
                        'label'         => __('Private'),
                        'note'          => $palletDelivery->internal_notes ?? '',
                        'editable'      => true,
                        'color'         => 'purple',
                        'field'         => 'internal_notes'
                    ],
                ],


                PalletDeliveryTabsEnum::PALLETS->value => $this->tab == PalletDeliveryTabsEnum::PALLETS->value ?
                    fn () => PalletsResource::collection(IndexPallets::run($palletDelivery, 'pallets'))
                    : Inertia::lazy(fn () => PalletsResource::collection(IndexPallets::run($palletDelivery, 'pallets'))),
            ]
        )->table(
            IndexPallets::make()->tableStructure(
                $palletDelivery,
                prefix: PalletDeliveryTabsEnum::PALLETS->value
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
            'grp.org.fulfilments.show.crm.customers.show.pallet-deliveries.show' =>
            array_merge(
                ShowFulfilmentCustomer::make()->getBreadcrumbs(
                    Arr::only($routeParameters, ['organisation', 'fulfilment', 'fulfilmentCustomer'])
                ),
                $headCrumb(
                    $palletDelivery,
                    [
                        'index' => [
                            'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet-deliveries.index',
                            'parameters' => Arr::only(
                                $routeParameters,
                                ['organisation', 'fulfilment', 'fulfilmentCustomer']
                            )
                        ],
                        'model' => [
                            'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet-deliveries.show',
                            'parameters' => Arr::only(
                                $routeParameters,
                                ['organisation', 'fulfilment', 'fulfilmentCustomer', 'palletDelivery']
                            )
                        ]
                    ],
                    $suffix
                )
            ),
            'grp.org.warehouses.show.fulfilment.pallet-deliveries.show' =>
            array_merge(
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
