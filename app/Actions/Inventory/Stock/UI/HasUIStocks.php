<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Stock\UI;

use App\Actions\UI\Inventory\InventoryDashboard;

trait HasUIStocks
{
    public function getBreadcrumbs(): array
    {
        return array_merge(
            (new InventoryDashboard())->getBreadcrumbs(),
            [
                'grp.org.inventory.stocks.index' => [
                    'route'      => 'grp.org.inventory.stocks.index',
                    'modelLabel' => [
                        'label' => __('stocks')
                    ],
                ],
            ]
        );
    }
}
