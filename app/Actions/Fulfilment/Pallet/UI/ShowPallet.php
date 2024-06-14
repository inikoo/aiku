<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet\UI;

use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\StoredItem\UI\IndexStoredItems;
use App\Actions\Fulfilment\WithFulfilmentCustomerSubNavigation;
use App\Actions\Helpers\History\IndexHistory;
use App\Actions\OrgAction;
use App\Actions\UI\Fulfilment\ShowFulfilmentDashboard;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\UI\Fulfilment\PalletTabsEnum;
use App\Http\Resources\Fulfilment\PalletResource;
use App\Http\Resources\Fulfilment\StoredItemResource;
use App\Http\Resources\History\HistoryResource;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Pallet $pallet
 */
class ShowPallet extends OrgAction
{
    use WithFulfilmentCustomerSubNavigation;

    public Customer|null $customer = null;
    private Warehouse|Organisation|FulfilmentCustomer|Fulfilment $parent;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->parent instanceof FulfilmentCustomer) {
            $this->canEdit = $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.stored-items.edit");

            return $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.stored-items.view");
        } elseif ($this->parent instanceof Warehouse) {
            $this->canEdit       = $request->user()->hasPermissionTo("fulfilment.{$this->warehouse->id}.stored-items.edit");
            $this->allowLocation = $request->user()->hasPermissionTo("locations.{$this->warehouse->id}.view");
            return $request->user()->hasPermissionTo("fulfilment.{$this->warehouse->id}.stored-items.view");
        }

        $this->canEdit = $request->user()->hasPermissionTo("fulfilment.{$this->organisation->id}.stored-items.edit");

        return $request->user()->hasPermissionTo("fulfilment.{$this->organisation->id}.stored-items.view");
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouse(Organisation $organisation, Warehouse $warehouse, Pallet $pallet, ActionRequest $request): Pallet
    {
        $this->parent = $warehouse;
        $this->initialisationFromWarehouse($warehouse, $request)->withTab(PalletTabsEnum::values());

        return $this->handle($pallet);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inLocation(Organisation $organisation, Warehouse $warehouse, Location $location, Pallet $pallet, ActionRequest $request): Pallet
    {
        $this->parent = $warehouse;
        $this->initialisationFromWarehouse($warehouse, $request)->withTab(PalletTabsEnum::values());

        return $this->handle($pallet);
    }

    public function asController(Organisation $organisation, Warehouse $warehouse, Fulfilment $fulfilment, Pallet $pallet, ActionRequest $request): Pallet
    {
        $this->parent = $organisation;
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(PalletTabsEnum::values());

        return $this->handle($pallet);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, PalletDelivery $palletDelivery, Pallet $pallet, ActionRequest $request): Pallet
    {
        $this->parent = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(PalletTabsEnum::values());

        return $this->handle($pallet);
    }

    public function handle(Pallet $pallet): Pallet
    {
        return $pallet;
    }


    public function htmlResponse(Pallet $pallet, ActionRequest $request): Response
    {
        // dd($pallet->status->statusIcon()[$pallet->status->value]);
        $subNavigation = [];

        if ($this->parent instanceof FulfilmentCustomer) {
            $subNavigation = $this->getFulfilmentCustomerSubNavigation($this->parent, $request);
        }

        $routeName = null;
        if ($this->parent instanceof Warehouse) {
            $routeName = 'grp.org.warehouses.show.fulfilment.pallets.edit';
        } elseif ($this->parent instanceof Fulfilment) {
            $routeName = 'grp.org.fulfilments.show.operations.pallets.edit';
        }


        return Inertia::render(
            'Org/Fulfilment/Pallet',
            [
                'title'                         => __('pallets'),
                'breadcrumbs'                   => $this->getBreadcrumbs(
                    $this->parent,
                    request()->route()->getName(),
                    request()->route()->originalParameters()
                ),
                'navigation'                            => [
                    'previous' => $this->getPrevious($pallet, $request),
                    'next'     => $this->getNext($pallet, $request),
                ],
                'pageHead'                      => [
                    'icon'          =>
                        [
                            'icon'    => ['fal', 'fa-pallet'],
                            'tooltip' => __('Pallet')
                        ],
                    'title'         => $this->pallet->reference,
                    'model'         => __('Pallet'),
                    'iconRight'     => $pallet->status->statusIcon()[$pallet->status->value],
                    'noCapitalise'  => true,
                    'after_title'   => [
                        'label'     => '(' . $this->pallet->customer_reference . ')'
                    ],
                    'subNavigation' => $subNavigation,
                    'actions'       => [
                        // [
                        //     'type'    => 'button',
                        //     'style'   => 'cancel',
                        //     'tooltip' => __('return to customer'),
                        //     'label'   => $this->pallet->status == PalletStatusEnum::RETURNED ? __('returned') : __('return to customer'),
                        //     'route'   => [
                        //         'name'       => 'grp.fulfilment.stored-items.setReturn',
                        //         'parameters' => array_values(request()->route()->originalParameters())
                        //     ],
                        //     'disabled' => $this->pallet->status == PalletStatusEnum::RETURNED
                        // ],
                        [
                            'type'    => 'button',
                            'style'   => 'edit',
                            'tooltip' => __('edit pallet'),
                            'label'   => __('Edit'),
                            'route'   => [
                                'name'       => $routeName,
                                'parameters' => array_values(request()->route()->originalParameters())
                            ]
                        ],
                        // [
                        //     'type'    => 'button',
                        //     'style'   => 'delete',
                        //     'tooltip' => __('set as damaged'),
                        //     'label'   => $this->pallet->status == PalletStatusEnum::DAMAGED ? __('damaged') : __('set as damaged'),
                        //     'route'   => [
                        //         'name'       => 'grp.fulfilment.stored-items.setDamaged',
                        //         'parameters' => array_values(request()->route()->originalParameters())
                        //     ],
                        //     'disabled' => $this->pallet->status == PalletStatusEnum::DAMAGED
                        // ],
                    ],
                ],
                'tabs'                          => [
                    'current'    => $this->tab,
                    'navigation' => PalletTabsEnum::navigation(),
                ],
                PalletTabsEnum::SHOWCASE->value => $this->tab == PalletTabsEnum::SHOWCASE->value ?
                    fn () => $this->jsonResponse($pallet) : Inertia::lazy(fn () => $this->jsonResponse($pallet)),

                PalletTabsEnum::STORED_ITEMS->value => $this->tab == PalletTabsEnum::STORED_ITEMS->value ?
                    fn () => StoredItemResource::collection(IndexStoredItems::run($pallet->fulfilmentCustomer, PalletTabsEnum::STORED_ITEMS->value))
                    : Inertia::lazy(fn () => StoredItemResource::collection(IndexStoredItems::run($pallet->fulfilmentCustomer, PalletTabsEnum::STORED_ITEMS->value))),

                PalletTabsEnum::HISTORY->value => $this->tab == PalletTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($this->pallet))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($this->pallet)))

            ]
        )->table(IndexHistory::make()->tableStructure(prefix: PalletTabsEnum::HISTORY->value))
            ->table(IndexStoredItems::make()->tableStructure($pallet->storedItems, prefix: PalletTabsEnum::STORED_ITEMS->value));
    }


    public function jsonResponse(Pallet $pallet): PalletResource
    {
        return new PalletResource($pallet);
    }

    public function getBreadcrumbs(Organisation|Warehouse|Fulfilment|FulfilmentCustomer $parent, string $routeName, array $routeParameters, string $suffix = ''): array
    {
        $pallet = Pallet::where('slug', $routeParameters['pallet'])->first();

        return match (class_basename($parent)) {
            'Warehouse'    => $this->getBreadcrumbsFromWarehouse($pallet, $routeName, $suffix),
            'Organisation' => $this->getBreadcrumbsFromFulfilment($pallet, $routeName, $suffix),
            default        => $this->getBreadcrumbsFromFulfilmentCustomer($pallet, $routeName, $suffix),
        };
    }

    public function getBreadcrumbsFromWarehouse(Pallet $pallet, $routeName, $suffix = null): array
    {
        return array_merge(
            ShowFulfilmentDashboard::make()->getBreadcrumbs(request()->route()->originalParameters()),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name'       => 'grp.org.warehouses.show.fulfilment.pallets.index',
                                'parameters' => array_values(request()->route()->originalParameters())
                            ],
                            'label' => __('Pallet')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'grp.org.warehouses.show.fulfilment.pallets.show',
                                'parameters' => array_values(request()->route()->originalParameters())
                            ],
                            'label' => $pallet->reference,
                        ],
                    ],
                    'suffix'         => $suffix,
                ],
            ]
        );
    }

    public function getBreadcrumbsFromFulfilment(Pallet $pallet, $routeName, $suffix = null): array
    {
        return array_merge(
            ShowFulfilment::make()->getBreadcrumbs(request()->route()->originalParameters()),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name'       => 'grp.org.warehouses.show.fulfilment.pallets.index',
                                'parameters' => array_values(request()->route()->originalParameters())
                            ],
                            'label' => __('Pallets')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'grp.org.warehouses.show.fulfilment.pallets.show',
                                'parameters' => array_values(request()->route()->originalParameters())
                            ],
                            'label' => $pallet->reference,
                        ],
                    ],
                    'suffix'         => $suffix,
                ],
            ]
        );
    }

    public function getBreadcrumbsFromFulfilmentCustomer(Pallet $pallet, $routeName, $suffix = null): array
    {
        return array_merge(
            ShowFulfilmentCustomer::make()->getBreadcrumbs(request()->route()->originalParameters()),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallets.index',
                                'parameters' => array_values(request()->route()->originalParameters())
                            ],
                            'label' => __('Pallets')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallets.show',
                                'parameters' => array_values(request()->route()->originalParameters())
                            ],
                            'label' => $pallet->reference,
                        ],
                    ],
                    'suffix'         => $suffix,
                ],
            ]
        );
    }

    public function getPrevious(Pallet $pallet, ActionRequest $request): ?array
    {
        $previous = Pallet::where('id', '<', $pallet->id)->orderBy('id', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(Pallet $pallet, ActionRequest $request): ?array
    {
        $next = Pallet::where('id', '>', $pallet->id)->orderBy('id')->first();
        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Pallet $pallet, string $routeName): ?array
    {
        if(!$pallet) {
            return null;
        }
        return match ($routeName) {
            'grp.org.fulfilments.show.crm.customers.show.pallets.show'=> [
                'label'=> $pallet->slug,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'organisation'       => $pallet->organisation->slug,
                        'fulfilment'         => $pallet->fulfilment->slug,
                        'fulfilmentCustomer' => $pallet->fulfilmentCustomer->slug,
                        'pallet'             => $pallet->slug
                    ]
                ]
            ],
            'grp.org.warehouses.show.fulfilment.pallets.show'=> [
                'label'=> $pallet->slug,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'organisation'       => $pallet->organisation->slug,
                        'warehouse'          => $pallet->warehouse->slug,
                        'pallet'             => $pallet->slug
                    ]
                ]
            ],

            'grp.org.fulfilments.show.operations.pallets.show'=> [
                'label'=> $pallet->number,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'organisation'=> $pallet->organisation->slug,
                        'fulfilment'  => $pallet->fulfilment->slug,
                        'pallet'      =>  $pallet->slug
                    ]

                ]
            ],

            // 'grp.org.fulfilments.show.crm.customers.show.invoices.show'=> [
            //     'label'=> $invoice->number,
            //     'route'=> [
            //         'name'      => $routeName,
            //         'parameters'=> [
            //             'organisation'       => $invoice->organisation->slug,
            //             'fulfilment'         => $this->parent->slug,
            //             'fulfilmentCustomer' => $this->parent->slug,
            //             'invoice'            => $invoice->slug
            //         ]

            //     ]
            // ],
            default => null,
        };
    }
}
