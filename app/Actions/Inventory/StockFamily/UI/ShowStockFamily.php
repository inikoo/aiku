<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 16 Mar 2023 13:21:47 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\StockFamily\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\Inventory\StockFamilyResource;
use App\Http\Resources\Inventory\StockResource;
use App\Models\Inventory\StockFamily;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;

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
        $this->initialisation($request);
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
                'stockFamily' => new StockFamilyResource($this->stockFamily),

            ]
        );
    }


    #[Pure] public function jsonResponse(): StockResource
    {
        return new StockResource($this->stockFamily);
    }
}
