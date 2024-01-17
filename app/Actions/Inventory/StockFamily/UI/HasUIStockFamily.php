<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:06:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\StockFamily\UI;

use App\Actions\UI\Inventory\ShowInventoryDashboard;
use App\Models\Inventory\StockFamily;

trait HasUIStockFamily
{
    public function getBreadcrumbs(StockFamily $stockFamily): array
    {
        return array_merge(
            (new ShowInventoryDashboard())->getBreadcrumbs(),
            [
                'grp.oms.stocks.show' => [
                    'route'           => 'grp.oms.stock-families.show',
                    'routeParameters' => $stockFamily->slug,
                    'name'            => $stockFamily->code,
                    'index'           => [
                        'route'   => 'grp.oms.stock-families.index',
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
