<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\OrgStock\UI;

use App\Actions\Inventory\UI\ShowInventoryDashboard;

trait HasUIStocks
{
    public function getBreadcrumbs(): array
    {
        return array_merge(
            (new ShowInventoryDashboard())->getBreadcrumbs(),
            [
                'grp.org.inventory.org_stocks.index' => [
                    'route'      => 'grp.org.inventory.org_stocks.index',
                    'modelLabel' => [
                        'label' => __('stocks')
                    ],
                ],
            ]
        );
    }
}
