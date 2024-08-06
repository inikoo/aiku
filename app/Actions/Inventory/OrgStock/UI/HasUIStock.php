<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:06:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\OrgStock\UI;

use App\Actions\Inventory\UI\ShowInventoryDashboard;
use App\Models\SupplyChain\Stock;

trait HasUIStock
{
    public function getBreadcrumbs(Stock $stock): array
    {
        return array_merge(
            (new ShowInventoryDashboard())->getBreadcrumbs(),
            [
                'grp.org.inventory.org-stocks.show' => [
                    'route'           => 'grp.org.inventory.org-stocks.show',
                    'routeParameters' => $stock->id,
                    'name'            => $stock->code,
                    'index'           => [
                        'route'   => 'grp.org.inventory.org_stocks.index',
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
