<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 16 Mar 2023 13:21:47 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\StockFamily\UI;

use App\Actions\InertiaAction;
use App\Actions\Inventory\Location\UI\IndexLocations;
use App\Enums\UI\StockFamilyTabsEnum;
use App\Http\Resources\Inventory\LocationResource;
use App\Http\Resources\Inventory\StockResource;
use App\Models\Inventory\StockFamily;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property StockFamily $stockFamily
 */
class ShowStockFamily extends InertiaAction
{
    use HasUIStockFamily;

    private StockFamily $stockFamily;

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('inventory.stock-families.edit');
        return $request->user()->hasPermissionTo("inventory.view");
    }

    public function asController(StockFamily $stockFamily, ActionRequest $request): void
    {
        $this->initialisation($request)->withTab(StockFamilyTabsEnum::values());
        $this->stockFamily = $stockFamily;
    }

    public function htmlResponse(): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Inventory/StockFamily',
            [
                'title'       => __('stock family'),
                'breadcrumbs' => $this->getBreadcrumbs($this->stockFamily),
                'pageHead'    => [
                    'icon'  => 'fal fa-inventory',
                    'title' => $this->stockFamily->code,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,
                    'meta'  => [
                        [
                            'name'     => trans_choice('stock | stocks', $this->stockFamily->stats->number_stocks),
                            'number'   => $this->stockFamily->stats->number_stocks,
                            'href'     => [
                                'inventory.stock-families.show.stocks.index',
                                $this->stockFamily->slug
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

                StockFamilyTabsEnum::LOCATIONS->value => $this->tab == StockFamilyTabsEnum::LOCATIONS->value ?
                    fn () => LocationResource::collection(IndexLocations::run($this->stockFamily))
                    : Inertia::lazy(fn () => LocationResource::collection(IndexLocations::run($this->stockFamily))),

/*
                StockFamilyTabsEnum::PRODUCTS->value => $this->tab == StockFamilyTabsEnum::PRODUCTS->value ?
                    fn () => ProductResource::collection(IndexProducts::run($this->stockFamily))
                    : Inertia::lazy(fn () => ProductResource::collection(IndexProducts::run($this->stockFamily))),


                StockFamilyTabsEnum::PRODUCT_FAMILIES->value => $this->tab == StockFamilyTabsEnum::PRODUCT_FAMILIES->value ?
                    fn () => FamilyResource::collection(IndexFamilies::run($this->stockFamily))
                    : Inertia::lazy(fn () => FamilyResource::collection(IndexFamilies::run($this->stockFamily))),
*/


            ]
        )->table(IndexLocations::make()->tableStructure());
        //->table(IndexProducts::make()->tableStructure($this->stockFamily))
        //   ->table(IndexFamilies::make()->tableStructure($this->stockFamily));
    }


    #[Pure] public function jsonResponse(): StockResource
    {
        return new StockResource($this->stockFamily);
    }
}
