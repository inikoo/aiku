<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 15:27:27 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Stock\UI;

use App\Actions\Helpers\History\IndexHistories;
use App\Actions\InertiaAction;
use App\Actions\Inventory\StockFamily\UI\ShowStockFamily;
use App\Actions\Procurement\Agent\UI\GetAgentShowcase;
use App\Actions\UI\Inventory\InventoryDashboard;
use App\Enums\UI\StockTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\Inventory\StockResource;
use App\Models\Inventory\Stock;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Stock $stock
 */

class ShowStock extends InertiaAction
{
    private Stock $stock;

    public function handle(Stock $stock): Stock
    {
        return $stock;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('inventory.stocks.edit');

        return $request->user()->hasPermissionTo("inventory.stocks.view");
    }

    public function asController(Stock $stock, ActionRequest $request): Stock
    {
        $this->initialisation($request)->withTab(StockTabsEnum::values());

        return $this->handle($stock);
    }

    public function htmlResponse(Stock $stock, ActionRequest $request): Response
    {
        return Inertia::render(
            'Inventory/Stock',
            [
                 'title'       => __('stock'),
                 'breadcrumbs' => $this->getBreadcrumbs($request->route()->getName(), $request->route()->originalParameters()),
                 'navigation'  => [
                     'previous' => $this->getPrevious($stock, $request),
                     'next'     => $this->getNext($stock, $request),
                 ],
                 'pageHead'    => [
                     'icon'    => 'fal fa-box',
                     'title'   => $this->stock->code,
                     'actions' => [
                         $this->canEdit ? [
                             'type'  => 'button',
                             'style' => 'edit',
                             'route' => [
                                 'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                                 'parameters' => array_values($this->originalParameters)
                             ]
                         ] : false,
                         $this->canDelete ? [
                             'type'  => 'button',
                             'style' => 'delete',
                             'route' => [
                                 'name'       => 'inventory.warehouses.show.warehouse-areas.remove',
                                 'parameters' => array_values($this->originalParameters)
                             ]

                         ] : false
                     ]
                 ],
                 'tabs'=> [
                     'current'    => $this->tab,
                     'navigation' => StockTabsEnum::navigation()

                 ],
                 StockTabsEnum::SHOWCASE->value => $this->tab == StockTabsEnum::SHOWCASE->value ?
                     fn () => GetAgentShowcase::run($stock)
                     : Inertia::lazy(fn () => GetAgentShowcase::run($stock)),

                 StockTabsEnum::HISTORY->value => $this->tab == StockTabsEnum::HISTORY->value ?
                     fn () => HistoryResource::collection(IndexHistories::run($stock))
                     : Inertia::lazy(fn () => HistoryResource::collection(IndexHistories::run($stock)))


             ]
        )->table();
    }


    public function jsonResponse(Stock $stock): StockResource
    {
        return new StockResource($stock);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, $suffix = null): array
    {
        $headCrumb = function (Stock $stock, array $routeParameters, $suffix) {
            return [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('stocks')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $stock->slug,
                        ],
                    ],
                    'suffix' => $suffix,

                ],
            ];
        };

        return match ($routeName) {
            'inventory.stocks.show' =>
            array_merge(
                (new InventoryDashboard())->getBreadcrumbs(),
                $headCrumb(
                    $routeParameters['stock'],
                    [
                        'index' => [
                            'name'       => 'inventory.stocks.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'inventory.stocks.show',
                            'parameters' => [
                                $routeParameters['stock']->slug
                            ]
                        ]
                    ],
                    $suffix
                )
            ),
            'inventory.stock-families.show.stocks.show' =>
            array_merge(
                (new ShowStockFamily())->getBreadcrumbs($routeParameters['stocksFamily']),
                $headCrumb(
                    $routeParameters['stock'],
                    [
                        'index' => [
                            'name'       => 'inventory.stock-families.show.stocks.index',
                            'parameters' => [
                                $routeParameters['stocksFamily']->slug
                            ]
                        ],
                        'model' => [
                            'name'       => 'inventory.stock-families.show.stocks.show',
                            'parameters' => [
                                $routeParameters['stocksFamily']->slug,
                                $routeParameters['stock']->slug
                            ]
                        ]
                    ],
                    $suffix
                )
            ),
            default => []
        };
    }

    public function getPrevious(Stock $stock, ActionRequest $request): ?array
    {
        $previous = Stock::where('code', '<', $stock->code)->when(true, function ($query) use ($stock, $request) {
            if ($request->route()->getName() == 'inventory.stock-families.show.stocks.show') {
                $query->where('stock_family_id', $stock->stockFamily->id);
            }
        })->orderBy('code', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Stock $stock, ActionRequest $request): ?array
    {
        $next = Stock::where('code', '>', $stock->code)->when(true, function ($query) use ($stock, $request) {
            if ($request->route()->getName() == 'inventory.stock-families.show.stocks.show') {
                $query->where('stock_family_id', $stock->stockFamily->id);
            }
        })->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Stock $stock, string $routeName): ?array
    {
        if (!$stock) {
            return null;
        }

        return match ($routeName) {
            'inventory.stocks.show' => [
                'label' => $stock->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'stock' => $stock->slug
                    ]
                ]
            ],
            'inventory.stock-families.show.stocks.show' => [
                'label' => $stock->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'stockFamily'   => $stock->stockFamily->slug,
                        'stock'         => $stock->slug
                    ]

                ]
            ]
        };
    }
}
