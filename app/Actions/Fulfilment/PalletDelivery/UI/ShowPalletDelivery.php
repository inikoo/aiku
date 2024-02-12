<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 17:41:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery\UI;

use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\Pallet\UI\IndexPallets;
use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\OrgAction;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Enums\UI\PalletDeliveryTabsEnum;
use App\Http\Resources\Fulfilment\PalletDeliveriesResource;
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
    private Warehouse|FulfilmentCustomer $parent;

    public function handle(PalletDelivery $palletDelivery): PalletDelivery
    {
        return $palletDelivery;
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

    public function asController(Organisation $organisation, Warehouse $warehouse, PalletDelivery $palletDelivery, ActionRequest $request): PalletDelivery
    {
        $this->parent = $warehouse;
        $this->initialisationFromWarehouse($warehouse, $request)->withTab(PalletDeliveryTabsEnum::values());

        return $this->handle($palletDelivery);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, PalletDelivery $palletDelivery, ActionRequest $request): PalletDelivery
    {
        $this->parent = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(PalletDeliveryTabsEnum::values());

        return $this->handle($palletDelivery);
    }

    public function htmlResponse(PalletDelivery $palletDelivery, ActionRequest $request): Response
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
                    'container' => $container,
                    'title'     => __($palletDelivery->reference),
                    'icon'      => [
                        'icon'  => ['fal', 'fa-truck-couch'],
                        'title' => __($palletDelivery->reference)
                    ],
                    'edit' => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                            'parameters' => array_values($request->route()->originalParameters())
                        ]
                    ] : false,
                    'actions' => $palletDelivery->state == PalletDeliveryStateEnum::IN_PROCESS ? [
                        [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('add multiple pallets'),
                            'label'   => __('add multiple pallets'),
                            'route'   => [
                                'name'       => 'grp.models.fulfilment-customer.pallet-delivery.multiple-pallets.store',
                                'parameters' => [
                                    'organisation'       => $palletDelivery->organisation->slug,
                                    'fulfilment'         => $palletDelivery->fulfilment->slug,
                                    'fulfilmentCustomer' => $palletDelivery->fulfilmentCustomer->slug,
                                    'palletDelivery'     => $palletDelivery->reference
                                ]
                            ]
                        ],
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
                                            'organisation'       => $palletDelivery->organisation->slug,
                                            'fulfilment'         => $palletDelivery->fulfilment->slug,
                                            'fulfilmentCustomer' => $palletDelivery->fulfilmentCustomer->slug,
                                            'palletDelivery'     => $palletDelivery->reference
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
                                            'organisation'       => $palletDelivery->organisation->slug,
                                            'fulfilment'         => $palletDelivery->fulfilment->slug,
                                            'fulfilmentCustomer' => $palletDelivery->fulfilmentCustomer->slug,
                                            'palletDelivery'     => $palletDelivery->reference
                                        ]
                                    ]
                                ],
                            ]
                        ],
                        $palletDelivery->pallets()->count() > 0 ? [
                            'type'    => 'button',
                            'style'   => 'save',
                            'tooltip' => __('submit'),
                            'label'   => __('submit'),
                            'key'     => 'action',
                            'route'   => [
                                'method'     => 'post',
                                'name'       => 'grp.models.fulfilment-customer.pallet-delivery.submit',
                                'parameters' => [
                                    'organisation'       => $palletDelivery->organisation->slug,
                                    'fulfilment'         => $palletDelivery->fulfilment->slug,
                                    'fulfilmentCustomer' => $palletDelivery->fulfilmentCustomer->slug,
                                    'palletDelivery'     => $palletDelivery->reference
                                ]
                            ]
                        ] : [],
                    ] : [
                        $palletDelivery->state == PalletDeliveryStateEnum::SUBMITTED ? [
                                'type'    => 'button',
                                'style'   => 'save',
                                'tooltip' => __('confirm'),
                                'label'   => __('confirm'),
                                'key'     => 'action',
                                'route'   => [
                                    'method'     => 'post',
                                    'name'       => 'grp.models.fulfilment-customer.pallet-delivery.confirm',
                                    'parameters' => [
                                        'organisation'       => $palletDelivery->organisation->slug,
                                        'fulfilment'         => $palletDelivery->fulfilment->slug,
                                        'fulfilmentCustomer' => $palletDelivery->fulfilmentCustomer->slug,
                                        'palletDelivery'     => $palletDelivery->reference
                                    ]
                                ]
                        ] : [],
                    ],
                ],

                'updateRoute' => [
                    'route' => [
                        'name'       => 'grp.models.fulfilment-customer.pallet-delivery.timeline.update',
                        'parameters' => [
                            'organisation'       => $palletDelivery->organisation->slug,
                            'fulfilment'         => $palletDelivery->fulfilment->slug,
                            'fulfilmentCustomer' => $palletDelivery->fulfilmentCustomer->slug,
                            'palletDelivery'     => $palletDelivery->reference
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
                            'organisation'       => $palletDelivery->organisation->slug,
                            'fulfilment'         => $palletDelivery->fulfilment->slug,
                            'fulfilmentCustomer' => $palletDelivery->fulfilmentCustomer->slug,
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

                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => PalletDeliveryTabsEnum::navigation()
                ],

                'data' => PalletDeliveryResource::make($palletDelivery),

                PalletDeliveryTabsEnum::PALLETS->value => $this->tab == PalletDeliveryTabsEnum::PALLETS->value ?
                    fn () => PalletsResource::collection(IndexPallets::run($palletDelivery))
                    : Inertia::lazy(fn () => PalletsResource::collection(IndexPallets::run($palletDelivery))),
            ]
        )->table(
            IndexPallets::make()->tableStructure(
                $palletDelivery,
                prefix: PalletDeliveryTabsEnum::PALLETS->value
            )
        );
    }


    public function jsonResponse(PalletDelivery $palletDelivery): PalletDeliveriesResource
    {
        return new PalletDeliveriesResource($palletDelivery);
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
                            'label' => $palletDelivery->reference,
                        ],

                    ],
                    'suffix' => $suffix
                ],
            ];
        };

        $palletDelivery = PalletDelivery::where('reference', $routeParameters['palletDelivery'])->first();


        return match ($routeName) {
            'grp.org.fulfilments.show.crm.customers.show.pallet-deliveries.show' => array_merge(
                ShowFulfilmentCustomer::make()->getBreadcrumbs(Arr::only($routeParameters, ['organisation', 'fulfilment', 'fulfilmentCustomer'])),
                $headCrumb(
                    $palletDelivery,
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
                        'palletDelivery' => $palletDelivery->reference
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
                        'palletDelivery'     => $palletDelivery->reference
                    ]

                ]
            ],
            default => []
        };
    }
}
