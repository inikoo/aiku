<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Warehouse\UI;

use App\Actions\UI\Inventory\InventoryDashboard;

trait HasUIWarehouses
{
    public function getBreadcrumbs(): array
    {
        return array_merge(
            (new InventoryDashboard())->getBreadcrumbs(),
            [
                'inventory.warehouses.index' => [
                    'route'      => 'inventory.warehouses.index',
                    'modelLabel' => [
                        'label' => __('warehouses')
                    ],
                ],
            ]
        );
    }
}
