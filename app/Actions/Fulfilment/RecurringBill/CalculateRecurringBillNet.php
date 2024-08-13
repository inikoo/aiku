<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 15 Jun 2024 00:11:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBill;

use App\Actions\OrgAction;
use App\Models\Fulfilment\RecurringBill;

class CalculateRecurringBillNet extends OrgAction
{
    public function handle(RecurringBill $recurringBill)
    {
        $recurringBill->load('transactions', 'palletDeliveries', 'palletReturns', 'taxCategory');

        $transactions = $recurringBill->transactions;
        $deliveries   = $recurringBill->palletDeliveries;
        $returns      = $recurringBill->palletReturns;
        $taxRate      = $recurringBill->taxCategory->rate;

        $deliveryGross    = 0;
        $returnGross      = 0;
        $deliveryGoods    = 0;
        $deliveryServices = 0;
        $returnGoods      = 0;
        $returnServices   = 0;

        if ($deliveries->isNotEmpty()) {
            $deliveryGross    = $deliveries->sum('gross_amount');
            $deliveryGoods    = $deliveries->sum('goods_amount');
            $deliveryServices = $deliveries->sum('services_amount');
        }

        if ($returns->isNotEmpty()) {
            $returnGross    = $returns->sum('gross_amount');
            $returnGoods    = $returns->sum('goods_amount');
            $returnServices = $returns->sum('services_amount');
        }

        $rentalNet   = $transactions->sum('net_amount');
        $rentalGross = $transactions->sum('gross_amount');

        $totalGoodsAmount    = $deliveryGoods    + $returnGoods;
        $totalServicesAmount = $deliveryServices + $returnServices;

        $netAmount   = $rentalNet   + $totalGoodsAmount + $totalServicesAmount;
        $grossAmount = $rentalGross + $deliveryGross + $returnGross;
        $taxAmount   = $netAmount * $taxRate;
        $totalAmount = $netAmount + $taxAmount;

        data_set($modelData, 'rental_amount', $rentalNet);
        data_set($modelData, 'net_amount', $netAmount);
        data_set($modelData, 'total_amount', $totalAmount);
        data_set($modelData, 'tax_amount', $taxAmount);
        data_set($modelData, 'services_amount', $totalServicesAmount);
        data_set($modelData, 'goods_amount', $totalGoodsAmount);
        data_set($modelData, 'gross_amount', $grossAmount);

        $recurringBill->update($modelData);
    }

    public function action(RecurringBill $recurringBill)
    {
        $this->asAction = true;
        $this->initialisationFromFulfilment($recurringBill->fulfilment, []);
        return $this->handle($recurringBill);
    }
}
