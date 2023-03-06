<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 08:11:23 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\StockFamily;

use App\Actions\UI\Inventory\InventoryDashboard;
use App\Actions\UI\WithInertia;
use App\Http\Resources\Inventory\StockFamilyResource;
use App\Http\Resources\Inventory\StockResource;
use App\Models\Inventory\StockFamily;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowStockFamily
{
    use AsAction;
    use WithInertia;


    private StockFamily $stockFamily;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("inventory.view");
    }

    public function asController(StockFamily $stockFamily): void
    {
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


    public function getBreadcrumbs(StockFamily $stockFamily): array
    {
        return array_merge(
            (new InventoryDashboard())->getBreadcrumbs(),
            [
                'inventory.stocks.show' => [
                    'route'           => 'inventory.stock-families.show',
                    'routeParameters' => $stockFamily->slug,
                    'name'            => $stockFamily->code,
                    'index'           => [
                        'route'   => 'inventory.stock-families.index',
                        'overlay' => __('stocks family list')
                    ],
                    'modelLabel'      => [
                        'label' => __('stock family')
                    ],
                ],
            ]
        );
    }
}
