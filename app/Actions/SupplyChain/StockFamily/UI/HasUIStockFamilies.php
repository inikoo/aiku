<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 22 Jan 2024 13:06:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\StockFamily\UI;

use App\Actions\UI\Inventory\ShowInventoryDashboard;

trait HasUIStockFamilies
{
    public function getBreadcrumbs(): array
    {
        return array_merge(
            (new ShowInventoryDashboard())->getBreadcrumbs(),
            [
                'grp.oms.stock-families.index' => [
                    'route'      => 'grp.org.inventory.org-stock-families.index',
                    'modelLabel' => [
                        'label' => __('families')
                    ],
                ],
            ]
        );
    }
}
