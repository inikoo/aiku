<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 23 Mar 2024 12:56:01 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\Stock\UI;

use App\Actions\Goods\HasGoodsAuthorisation;
use App\Actions\Goods\StockFamily\UI\ShowStockFamily;
use App\Actions\GrpAction;
use App\Actions\Helpers\History\IndexHistory;
use App\Actions\Inventory\OrgStock\UI\GetStockShowcase;
use App\Actions\Inventory\UI\ShowInventoryDashboard;
use App\Enums\UI\SupplyChain\StockTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\Inventory\OrgStockResource;
use App\Models\SupplyChain\Stock;
use App\Models\SupplyChain\StockFamily;
use App\Models\SysAdmin\Group;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowStock extends GrpAction
{
    use HasGoodsAuthorisation;


    private Group|StockFamily $parent;

    public function handle(Stock $stock): Stock
    {
        return $stock;
    }


    public function asController(Stock $stock, ActionRequest $request): Stock
    {
        $this->parent=group();
        $this->initialisation($this->parent, $request)->withTab(StockTabsEnum::values());

        return $this->handle($stock);
    }

    public function inStockFamily(StockFamily $stockFamily, Stock $stock, ActionRequest $request): Stock
    {
        $this->parent=$stockFamily;
        $this->initialisation(group(), $request);

        return $this->handle($stock);
    }

    public function htmlResponse(Stock $stock, ActionRequest $request): Response
    {

        return Inertia::render(
            'Inventory/Stock',
            [
                 'title'       => __('stock'),
                 'breadcrumbs' => $this->getBreadcrumbs(
                     $request->route()->getName(),
                     $request->route()->originalParameters()
                 ),
                 'navigation'  => [
                     'previous' => $this->getPrevious($stock, $request),
                     'next'     => $this->getNext($stock, $request),
                 ],
                 'pageHead'    => [
                     'icon'    => [
                         'title' => __('skus'),
                         'icon'  => 'fal fa-box'
                     ],
                     'title'   => $stock->slug,
                     'actions' => [
                         $this->canEdit ? [
                             'type'  => 'button',
                             'style' => 'edit',
                             'route' => [
                                 'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                                 'parameters' => array_values($request->route()->originalParameters())
                             ]
                         ] : false,
                         $this->canDelete ? [
                             'type'  => 'button',
                             'style' => 'delete',
                             'route' => [
                                 'name'       => 'grp.org.inventory.org-stock-families.show.stocks.remove',
                                 'parameters' => array_values($request->route()->originalParameters())
                             ]

                         ] : false
                     ]
                 ],
                 'tabs'=> [
                     'current'    => $this->tab,
                     'navigation' => StockTabsEnum::navigation()

                 ],
                 StockTabsEnum::SHOWCASE->value => $this->tab == StockTabsEnum::SHOWCASE->value ?
                     fn () => GetStockShowcase::run($stock)
                     : Inertia::lazy(fn () => GetStockShowcase::run($stock)),

                 StockTabsEnum::HISTORY->value => $this->tab == StockTabsEnum::HISTORY->value ?
                     fn () => HistoryResource::collection(IndexHistory::run($stock))
                     : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($stock)))


             ]
        )->table();
    }


    public function jsonResponse(Stock $stock): OrgStockResource
    {
        return new OrgStockResource($stock);
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
                            'label' => __('SKUs')
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
            'grp.goods.stocks.show' =>
            array_merge(
                (new ShowInventoryDashboard())->getBreadcrumbs(),
                $headCrumb(
                    $routeParameters['stock'],
                    [
                        'index' => [
                            'name'       => 'grp.org.inventory.org-stocks.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'grp.org.inventory.org-stocks.show',
                            'parameters' => [
                                $routeParameters['stock']->slug
                            ]
                        ]
                    ],
                    $suffix
                )
            ),
            'grp.org.inventory.org-stock-families.show.stocks.show' =>
            array_merge(
                (new ShowStockFamily())->getBreadcrumbs($routeParameters['stockFamily']),
                $headCrumb(
                    $routeParameters['stock'],
                    [
                        'index' => [
                            'name'       => 'grp.org.inventory.org-stock-families.show.stocks.index',
                            'parameters' => [
                                $routeParameters['stockFamily']->slug
                            ]
                        ],
                        'model' => [
                            'name'       => 'grp.org.inventory.org-stock-families.show.stocks.show',
                            'parameters' => [
                                $routeParameters['stockFamily']->slug,
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
            if ($request->route()->getName() == 'grp.org.inventory.org-stock-families.show.stocks.show') {
                $query->where('stock_family_id', $stock->stockFamily->id);
            }
        })->orderBy('code', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Stock $stock, ActionRequest $request): ?array
    {
        $next = Stock::where('code', '>', $stock->code)->when(true, function ($query) use ($stock, $request) {
            if ($request->route()->getName() == 'grp.org.inventory.org-stock-families.show.stocks.show') {
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
            'grp.org.inventory.org-stocks.show' => [
                'label' => $stock->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'stock' => $stock->slug
                    ]
                ]
            ],
            'grp.org.inventory.org-stock-families.show.stocks.show' => [
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
