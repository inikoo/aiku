<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 15:27:27 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Stock\UI;

use App\Actions\InertiaAction;
use App\Actions\Inventory\Location\UI\IndexLocations;
use App\Actions\Market\Product\UI\IndexProducts;
use App\Actions\Procurement\Agent\UI\GetAgentShowcase;
use App\Actions\Procurement\SupplierProduct\UI\IndexSupplierProducts;
use App\Enums\UI\StockTabsEnum;
use App\Http\Resources\Inventory\LocationResource;
use App\Http\Resources\Inventory\StockResource;
use App\Http\Resources\Market\ProductResource;
use App\Http\Resources\Procurement\SupplierProductResource;
use App\Models\Inventory\Stock;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Stock $stock
 */

class ShowStock extends InertiaAction
{
    use HasUIStock;

    private Stock $stock;

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('inventory.stocks.edit');

        return $request->user()->hasPermissionTo("inventory.stocks.view");
    }

    public function asController(Stock $stock, ActionRequest $request): void
    {
        $stock->load('locations');
        $this->initialisation($request)->withTab(StockTabsEnum::values());
        $this->stock    = $stock;
    }

    public function htmlResponse(): Response
    {
        $this->validateAttributes();



        return Inertia::render(
            'Inventory/Stock',
            [
                'title'       => __('stock'),
                'breadcrumbs' => $this->getBreadcrumbs($this->stock),
                'pageHead'    => [
                    'icon'  => 'fal fa-box',
                    'title' => $this->stock->code,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,
                ],
                'tabs'=> [
                    'current'    => $this->tab,
                    'navigation' => StockTabsEnum::navigation()

                ],
                StockTabsEnum::SHOWCASE->value => $this->tab == StockTabsEnum::SHOWCASE->value ?
                    fn () => GetAgentShowcase::run($this->stock)
                    : Inertia::lazy(fn () => GetAgentShowcase::run($this->stock)),

                StockTabsEnum::SUPPLIERS_PRODUCTS->value => $this->tab == StockTabsEnum::SUPPLIERS_PRODUCTS->value ?
                    fn () => SupplierProductResource::collection(IndexSupplierProducts::run($this->stock))
                    : Inertia::lazy(fn () => SupplierProductResource::collection(IndexSupplierProducts::run($this->stock))),

                StockTabsEnum::PRODUCTS->value => $this->tab == StockTabsEnum::PRODUCTS->value ?
                    fn () => ProductResource::collection(IndexProducts::run($this->stock))
                    : Inertia::lazy(fn () => ProductResource::collection(IndexProducts::run($this->stock))),

                StockTabsEnum::LOCATIONS->value => $this->tab == StockTabsEnum::LOCATIONS->value ?
                    fn () => LocationResource::collection(IndexLocations::run($this->stock))
                    : Inertia::lazy(fn () => LocationResource::collection(IndexLocations::run($this->stock))),


            ]
        )->table(IndexSupplierProducts::make()->tableStructure())
            ->table(IndexProducts::make()->tableStructure())
            ->table(IndexLocations::make()->tableStructure());
    }


    public function jsonResponse(): StockResource
    {
        return new StockResource($this->stock);
    }
}
