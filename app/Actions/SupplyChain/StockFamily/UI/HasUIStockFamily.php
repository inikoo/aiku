<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 22 Jan 2024 13:06:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\StockFamily\UI;

use App\Actions\UI\Inventory\ShowInventoryDashboard;
use App\Models\SupplyChain\StockFamily;

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
