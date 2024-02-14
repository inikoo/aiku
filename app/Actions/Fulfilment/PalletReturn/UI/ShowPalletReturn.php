<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 17:41:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn\UI;

use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\Pallet\UI\IndexPallets;
use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\OrgAction;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Enums\UI\PalletReturnTabsEnum;
use App\Http\Resources\Fulfilment\PalletDeliveriesResource;
use App\Http\Resources\Fulfilment\PalletReturnResource;
use App\Http\Resources\Fulfilment\PalletsResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowPalletReturn extends OrgAction
{
    private Warehouse|FulfilmentCustomer $parent;

    public function handle(PalletReturn $palletReturn): PalletReturn
    {
        return $palletReturn;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->parent instanceof FulfilmentCustomer) {
            $this->canEdit = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");

            return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.view");
        }
        if ($this->parent instanceof Warehouse) {
            $this->canEdit = $request->user()->hasPermissionTo("fulfilment.{$this->warehouse->id}.edit");

            return $request->user()->hasPermissionTo("fulfilment.{$this->warehouse->id}.view");
        }


        return false;
    }

    public function asController(Organisation $organisation, Warehouse $warehouse, PalletReturn $palletReturn, ActionRequest $request): PalletReturn
    {
        $this->parent = $warehouse;
        $this->initialisationFromWarehouse($warehouse, $request)->withTab(PalletReturnTabsEnum::values());

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
                    'container' => $container,
                    'title'     => __($palletReturn->reference),
                    'icon'      => [
                        'icon'  => ['fal', 'fa-truck-couch'],
                        'title' => __($palletReturn->reference)
                    ],
                    'edit' => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                            'parameters' => array_values($request->route()->originalParameters())
                        ]
                    ] : false,
                    'actions' => $palletReturn->state == PalletReturnStateEnum::IN_PROCESS ? [
                        [
                            'type'   => 'buttonGroup',
                            'key'    => 'upload-add',
                            'button' => [
                                [
                                    'type'  => 'button',
                                    'style' => 'primary',
                                    'icon'  => ['fal', 'fa-upload'],
                                    'label' => 'upload',
                                    'route' => [
                                        'name'       => 'grp.models.fulfilment-customer.pallet-delivery.pallet.import',
                                        'parameters' => [
                                            'organisation'       => $palletReturn->organisation->slug,
                                            'fulfilment'         => $palletReturn->fulfilment->slug,
                                            'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->id,
                                            'palletDelivery'     => $palletReturn->reference
                                        ]
                                    ]
                                ],
                                [
                                    'type'    => 'button',
                                    'style'   => 'primary',
                                    'icon'    => ['far', 'fa-layer-plus'],
                                    'label'   => 'multiple',
                                    'route'   => [
                                        'name'       => 'grp.models.fulfilment-customer.pallet-delivery.multiple-pallets.store',
                                        'parameters' => [
                                            'organisation'       => $palletReturn->organisation->slug,
                                            'fulfilment'         => $palletReturn->fulfilment->slug,
                                            'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->id,
                                            'palletDelivery'     => $palletReturn->reference
                                        ]
                                    ]
                                ],
                                [
                                    'type'  => 'button',
                                    'style' => 'create',
                                    'label' => __('add pallet'),
                                    'route' => [
                                        'name'       => 'grp.models.fulfilment-customer.pallet-delivery.pallet.store',
                                        'parameters' => [
                                            'organisation'       => $palletReturn->organisation->slug,
                                            'fulfilment'         => $palletReturn->fulfilment->slug,
                                            'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->id,
                                            'palletDelivery'     => $palletReturn->reference
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
                                'name'       => 'grp.models.fulfilment-customer.pallet-delivery.submit',
                                'parameters' => [
                                    'organisation'       => $palletReturn->organisation->slug,
                                    'fulfilment'         => $palletReturn->fulfilment->slug,
                                    'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->id,
                                    'palletDelivery'     => $palletReturn->reference
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
                                    'name'       => 'grp.models.fulfilment-customer.pallet-delivery.confirm',
                                    'parameters' => [
                                        'organisation'       => $palletReturn->organisation->slug,
                                        'fulfilment'         => $palletReturn->fulfilment->slug,
                                        'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->id,
                                        'palletDelivery'     => $palletReturn->reference
                                    ]
                                ]
                        ] : [],
                    ],
                ],

                'updateRoute' => [
                    'route' => [
                        'name'       => 'grp.models.fulfilment-customer.pallet-delivery.timeline.update',
                        'parameters' => [
                            'organisation'       => $palletReturn->organisation->slug,
                            'fulfilment'         => $palletReturn->fulfilment->slug,
                            'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->id,
                            'palletDelivery'     => $palletReturn->reference
                        ]
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
                            'organisation'       => $palletReturn->organisation->slug,
                            'fulfilment'         => $palletReturn->fulfilment->slug,
                            'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->id,
                            'palletDelivery'     => $palletReturn->reference
                        ]
                    ],
                    'download' => [
                        'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet-deliveries.pallets.uploads.templates',
                        'parameters' => [
                            'organisation'       => $palletReturn->organisation->slug,
                            'fulfilment'         => $palletReturn->fulfilment->slug,
                            'fulfilmentCustomer' => $palletReturn->fulfilmentCustomer->id,
                            'palletDelivery'     => $palletReturn->reference
                        ]
                    ],
                ],

                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => PalletReturnTabsEnum::navigation()
                ],

                'data' => PalletReturnResource::make($palletReturn),

                PalletReturnTabsEnum::PALLETS->value => $this->tab == PalletReturnTabsEnum::PALLETS->value ?
                    fn () => PalletsResource::collection(IndexPallets::run($palletReturn))
                    : Inertia::lazy(fn () => PalletsResource::collection(IndexPallets::run($palletReturn))),
            ]
        )->table(
            IndexPallets::make()->tableStructure(
                $palletReturn,
                prefix: PalletReturnTabsEnum::PALLETS->value
            )
        );
    }


    public function jsonResponse(PalletReturn $palletReturn): PalletDeliveriesResource
    {
        return new PalletDeliveriesResource($palletReturn);
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
                            'label' => __('pallet deliveries')
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

        $palletReturn = PalletReturn::where('reference', $routeParameters['palletDelivery'])->first();


        return match ($routeName) {
            'grp.org.fulfilments.show.crm.customers.show.pallet-deliveries.show' => array_merge(
                ShowFulfilmentCustomer::make()->getBreadcrumbs(Arr::only($routeParameters, ['organisation', 'fulfilment', 'fulfilmentCustomer'])),
                $headCrumb(
                    $palletReturn,
                    [
                        'index' => [
                            'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet-deliveries.index',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment', 'fulfilmentCustomer'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet-deliveries.show',
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
                    $palletReturn,
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
                        'palletDelivery' => $palletReturn->reference
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
                        'palletDelivery'     => $palletReturn->reference
                    ]

                ]
            ],
            default => []
        };
    }
}
