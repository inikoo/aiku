<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:06:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\StockFamily\UI;

use App\Actions\UI\Inventory\InventoryDashboard;
use App\Models\Inventory\StockFamily;

trait HasUIStockFamily
{
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
