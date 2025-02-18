<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem\UI;

use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\WithFulfilmentCustomerSubNavigation;
use App\Actions\Helpers\History\UI\IndexHistory;
use App\Actions\OrgAction;
use App\Actions\UI\Fulfilment\ShowWarehouseFulfilmentDashboard;
use App\Enums\UI\Fulfilment\StoredItemTabsEnum;
use App\Http\Resources\Fulfilment\PalletsResource;
use App\Http\Resources\Fulfilment\StoredItemMovementsResource;
use App\Http\Resources\Fulfilment\StoredItemResource;
use App\Http\Resources\History\HistoryResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\StoredItem;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property StoredItem $storedItem
 */
class ShowStoredItem extends OrgAction
{
    use WithFulfilmentCustomerSubNavigation;
    private Warehouse|Organisation|FulfilmentCustomer|Fulfilment $parent;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->parent instanceof FulfilmentCustomer) {
            $this->canEdit = $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.edit");

            return
                (
                    $request->user()->tokenCan('root') or
                    $request->user()->authTo("human-resources.{$this->organisation->id}.view")
                );
        } elseif ($this->parent instanceof Warehouse) {
            $this->canEdit       = $request->user()->authTo("fulfilment.{$this->warehouse->id}.stored-items.edit");
            $this->allowLocation = $request->user()->authTo("locations.{$this->warehouse->id}.view");
            return $request->user()->authTo("fulfilment.{$this->warehouse->id}.stored-items.view");
        }

        return false;
    }

    public function asController(Organisation $organisation, Warehouse $warehouse, StoredItem $storedItem, ActionRequest $request): StoredItem
    {
        $this->parent = $warehouse;
        $this->initialisationFromWarehouse($warehouse, $request)->withTab(StoredItemTabsEnum::values());

        return $this->handle($storedItem);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, StoredItem $storedItem, ActionRequest $request): StoredItem
    {
        $this->parent = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(StoredItemTabsEnum::values());

        return $this->handle($storedItem);
    }

    public function handle(StoredItem $storedItem): StoredItem
    {
        return $storedItem;
    }

    public function htmlResponse(StoredItem $storedItem, ActionRequest $request): Response
    {
        $subNavigation = [];
        if ($this->parent instanceof FulfilmentCustomer) {
            $subNavigation = $this->getFulfilmentCustomerSubNavigation($this->parent, $request);
        }
        return Inertia::render(
            'Org/Fulfilment/StoredItem',
            [
                'title'       => __('stored item'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $this->parent,
                    request()->route()->getName(),
                    request()->route()->originalParameters()
                ),
                'pageHead'    => [
                    'icon'          =>
                        [
                            'icon'  => ['fal', 'fa-narwhal'],
                            'title' => __('stored item')
                        ],
                    'subNavigation' => $subNavigation,
                    'model'  => __('Customer\'s SKU'),
                    'title'  => $storedItem->slug,
                    'actions' => [


                        [
                            'type'    => 'button',
                            'style'   => 'edit',

                            'tooltip' => __('Edit stored items'),

                            'route'   => [
                                'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ],
                    ],
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => StoredItemTabsEnum::navigation(),
                ],

                'palletRoute' => [
                    'index' => [
                        'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallets.index',
                        'parameters' => [
                            'organisation'         => $request->route('organisation'),
                            'fulfilment'           => $request->route('fulfilment'),
                            'fulfilmentCustomer'   => $request->route('fulfilmentCustomer')
                        ]
                    ],
                ],

                'locationRoute' => [
                    'index' => [
                        'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallets.locations.index',
                        'parameters' => [
                            'organisation'         => $request->route('organisation'),
                            'fulfilment'           => $request->route('fulfilment'),
                            'fulfilmentCustomer'   => $request->route('fulfilmentCustomer')
                        ]
                    ],
                ],

                'update' => [
                    'name'       => 'grp.models.stored-items.move',
                    'parameters' => [
                        'storedItem'         => $storedItem->id
                    ]
                ],

                StoredItemTabsEnum::SHOWCASE->value => $this->tab == StoredItemTabsEnum::SHOWCASE->value ?
                    fn () => GetStoredItemShowcase::run($storedItem)
                    : Inertia::lazy(fn () => GetStoredItemShowcase::run($storedItem)),

                StoredItemTabsEnum::PALLETS->value => $this->tab == StoredItemTabsEnum::PALLETS->value ?
                    fn () => PalletsResource::collection(IndexStoredItemPallets::run($storedItem))
                    : Inertia::lazy(fn () => PalletsResource::collection(IndexStoredItemPallets::run($storedItem))),

                StoredItemTabsEnum::MOVEMENTS->value => $this->tab == StoredItemTabsEnum::MOVEMENTS->value ?
                    fn () => StoredItemMovementsResource::collection(IndexStoredItemMovements::run($storedItem))
                    : Inertia::lazy(fn () => StoredItemMovementsResource::collection(IndexStoredItemMovements::run($storedItem))),

                StoredItemTabsEnum::HISTORY->value => $this->tab == StoredItemTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($storedItem))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($storedItem)))

            ]
        )->table(IndexHistory::make()->tableStructure(prefix: StoredItemTabsEnum::HISTORY->value))
            ->table(IndexStoredItemMovements::make()->tableStructure($storedItem, prefix: StoredItemTabsEnum::MOVEMENTS->value))
            ->table(IndexStoredItemPallets::make()->tableStructure($storedItem, 'pallets'));
    }


    public function jsonResponse(StoredItem $storedItem): StoredItemResource
    {
        return new StoredItemResource($storedItem);
    }

    public function getBreadcrumbs(Organisation|Warehouse|Fulfilment|FulfilmentCustomer $parent, string $routeName, array $routeParameters, string $suffix = ''): array
    {
        $storedItem = StoredItem::where('slug', $routeParameters['storedItem'])->first();

        return match (class_basename($parent)) {
            'Warehouse'    => $this->getBreadcrumbsFromWarehouse($storedItem, $routeName, $suffix),
            default        => $this->getBreadcrumbsFromFulfilmentCustomer($storedItem, $routeName, $suffix),
        };
    }
    public function getBreadcrumbsFromFulfilmentCustomer(StoredItem $storedItem, $routeName, $suffix = null): array
    {
        return array_merge(
            ShowFulfilmentCustomer::make()->getBreadcrumbs(request()->route()->originalParameters()),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name'       => 'grp.org.fulfilments.show.crm.customers.show.stored-items.index',
                                'parameters' => array_values(request()->route()->originalParameters())
                            ],
                            'label' => __("Customer's SKUs")
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'grp.org.fulfilments.show.crm.customers.show.stored-items.show',
                                'parameters' => array_values(request()->route()->originalParameters())
                            ],
                            'label' => $storedItem->slug,
                        ],
                    ],
                    'suffix' => $suffix,
                ],
            ]
        );
    }

    public function getBreadcrumbsFromWarehouse(StoredItem $storedItem, $routeName, $suffix = null): array
    {
        return array_merge(
            ShowWarehouseFulfilmentDashboard::make()->getBreadcrumbs(request()->route()->originalParameters()),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name'       => 'grp.org.warehouses.show.inventory.stored_items.current.index',
                                'parameters' => array_values(request()->route()->originalParameters())
                            ],
                            'label' => __('Stored Item')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'grp.org.warehouses.show.inventory.stored_items.current.show',
                                'parameters' => array_values(request()->route()->originalParameters())
                            ],
                            'label' => $storedItem->reference,
                        ],
                    ],
                    'suffix'         => $suffix,
                ],
            ]
        );
    }
}
