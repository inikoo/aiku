<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:06:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Stock\UI;

use App\Actions\UI\Inventory\InventoryDashboard;
use App\Models\Inventory\Stock;

trait HasUIStock
{
    public function getBreadcrumbs(Stock $stock): array
    {
        return array_merge(
            (new InventoryDashboard())->getBreadcrumbs(),
            [
                'inventory.stocks.show' => [
                    'route'           => 'inventory.stocks.show',
                    'routeParameters' => $stock->id,
                    'name'            => $stock->code,
                    'index'           => [
                        'route'   => 'inventory.stocks.index',
                        'overlay' => __('stocks list')
                    ],
                    'modelLabel'      => [
                        'label' => __('stock')
                    ],
                ],
            ]
        );
    }
}
