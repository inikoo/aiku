<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 16 Mar 2023 13:21:47 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\StockFamily\UI;

use App\Actions\Helpers\History\IndexHistories;
use App\Actions\InertiaAction;
use App\Actions\Inventory\Stock\UI\IndexStocks;
use App\Actions\UI\Inventory\InventoryDashboard;
use App\Enums\UI\StockFamilyTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\Inventory\StockFamilyResource;
use App\Http\Resources\Inventory\StockResource;
use App\Models\Inventory\StockFamily;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property StockFamily $stockFamily
 */

class ShowStockFamily extends InertiaAction
{
    public function handle(StockFamily $stockFamily): StockFamily
    {
        return $stockFamily;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->can('inventory.stocks.edit');
        $this->canDelete = $request->user()->can('inventory.stocks.edit');

        return $request->user()->hasPermissionTo("inventory.stocks.view");
    }

    public function asController(StockFamily $stockFamily, ActionRequest $request): StockFamily
    {
        $this->initialisation($request)->withTab(StockFamilyTabsEnum::values());

        return $this->handle($stockFamily);
    }

    public function htmlResponse(StockFamily $stockFamily, ActionRequest $request): Response
    {

        return Inertia::render(
            'Inventory/StockFamily',
            [
                'title'                            => __('stock family'),
                'breadcrumbs'                      => $this->getBreadcrumbs($stockFamily),
                'navigation'                       => [
                    'previous' => $this->getPrevious($stockFamily, $request),
                    'next'     => $this->getNext($stockFamily, $request),
                ],
                'pageHead'    => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'fa-boxes-alt'],
                            'title' => __('stock family')
                        ],
                    'title'   => $stockFamily->name,
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
                                'name'       => 'inventory.stock-families.remove',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : false
                    ],
                    'meta'  => [
                        [
                            'name'     => trans_choice('stock | stocks', $stockFamily->stats->number_stocks),
                            'number'   => $stockFamily->stats->number_stocks,
                            'href'     => [
                                'inventory.stock-families.show.stocks.index',
                                $stockFamily->slug
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-box',
                                'tooltip' => __('stocks')
                            ]
                        ],
                    ]
                ],
                'tabs'=> [
                    'current'    => $this->tab,
                    'navigation' => StockFamilyTabsEnum::navigation()

                ],

                StockFamilyTabsEnum::SHOWCASE->value => $this->tab == StockFamilyTabsEnum::SHOWCASE->value ?
                    fn () => GetStockFamilyShowcase::run($stockFamily)
                    : Inertia::lazy(fn () => GetStockFamilyShowcase::run($stockFamily)),
                StockFamilyTabsEnum::STOCK->value => $this->tab == StockFamilyTabsEnum::STOCK->value
                    ?
                    fn () => StockResource::collection(
                        IndexStocks::run(
                            parent: $stockFamily,
                            prefix: 'stocks'
                        )
                    )
                    : Inertia::lazy(fn () => StockResource::collection(
                        IndexStocks::run(
                            parent: $stockFamily,
                            prefix: 'stocks'
                        )
                    )),
                StockFamilyTabsEnum::HISTORY->value => $this->tab == StockFamilyTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistories::run($stockFamily))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistories::run($stockFamily)))
            ]
        )->table();
    }


    public function jsonResponse(StockFamily $stockFamily): StockFamilyResource
    {
        return new StockFamilyResource($stockFamily);
    }

    public function getBreadcrumbs(StockFamily $stockFamily, $suffix = null): array
    {
        return array_merge(
            (new InventoryDashboard())->getBreadcrumbs(),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name' => 'inventory.stock-families.index',
                            ],
                            'label' => __('SKUs families'),
                            'icon'  => 'fal fa-bars'
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'inventory.stock-families.show',
                                'parameters' => [$stockFamily->slug]
                            ],
                            'label' => $stockFamily->code,
                            'icon'  => 'fal fa-bars'
                        ],
                    ],
                    'suffix'         => $suffix,

                ],
            ]
        );
    }

    public function getPrevious(StockFamily $stockFamily, ActionRequest $request): ?array
    {
        $previous = StockFamily::where('code', '<', $stockFamily->code)->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(StockFamily $stockFamily, ActionRequest $request): ?array
    {
        $next = StockFamily::where('code', '>', $stockFamily->code)->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?StockFamily $stockFamily, string $routeName): ?array
    {
        if (!$stockFamily) {
            return null;
        }

        return match ($routeName) {
            'inventory.stock-families.show' => [
                'label' => $stockFamily->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'stockFamily' => $stockFamily->slug
                    ]

                ]
            ]
        };
    }
}
