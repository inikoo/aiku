<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 05 Jul 2024 00:03:50 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order;

use App\Actions\OrgAction;
use App\Models\Ordering\Order;

class CalculateOrderTotalAmounts extends OrgAction
{
    public function handle(Order $order): void
    {
        $items       = $order->transactions()->get();
        $itemsNet    = $items->sum('net_amount');
        $itemsGross  = $items->sum('gross_amount');
        $tax         = $order->taxCategory->rate;



        $taxAmount   = $itemsNet * $tax;
        $totalAmount = $itemsNet + $taxAmount;
        $grpNet      = $itemsNet * $order->grp_exchange;
        $orgNet      = $itemsNet * $order->org_exchange;

        data_set($modelData, 'net_amount', $itemsNet);
        data_set($modelData, 'total_amount', $totalAmount);
        data_set($modelData, 'tax_amount', $taxAmount);
        data_set($modelData, 'goods_amount', $itemsNet);
        data_set($modelData, 'grp_net_amount', $grpNet);
        data_set($modelData, 'org_net_amount', $orgNet);
        data_set($modelData, 'gross_amount', $itemsGross);

        $order->update($modelData);
    }
}
