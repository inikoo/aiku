<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 05 Jul 2024 00:03:50 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\OrgAction;
use App\Enums\Fulfilment\FulfilmentTransaction\FulfilmentTransactionTypeEnum;
use App\Models\Fulfilment\PalletDelivery;

class CalculatePalletDeliveryNet extends OrgAction
{
    public function handle(PalletDelivery $palletDelivery)
    {
        $physicalGoods    = $palletDelivery->transactions()->where('type', FulfilmentTransactionTypeEnum::PRODUCT)->get();
        $physicalGoodsNet = $physicalGoods->sum('net_amount');
        $services         = $palletDelivery->transactions()->where('type', FulfilmentTransactionTypeEnum::SERVICE)->get();
        $servicesNet      = $services->sum('net_amount');
        $palletPriceTotal = 0;
        foreach ($palletDelivery->pallets as $pallet) {
            $discount         = $pallet->rentalAgreementClause ? $pallet->rentalAgreementClause->percentage_off / 100 : null;
            $rentalPrice      = $pallet->rental->price ?? 0;
            $palletPriceTotal += $rentalPrice - $rentalPrice * $discount;
        }
        $tax = $palletDelivery->taxCategory->rate;

        $net         = $physicalGoodsNet + $servicesNet + $palletPriceTotal;
        $taxAmount   = $net * $tax;
        $totalAmount = $net + $taxAmount;
        $grpNet      = $net * $palletDelivery->grp_exchange;
        $orgNet      = $net * $palletDelivery->org_exchange;

        data_set($modelData, 'net_amount', $net);
        data_set($modelData, 'total_amount', $totalAmount);
        data_set($modelData, 'tax_amount', $taxAmount);
        data_set($modelData, 'services_amount', $servicesNet);
        data_set($modelData, 'goods_amount', $physicalGoodsNet);
        data_set($modelData, 'grp_net_amount', $grpNet);
        data_set($modelData, 'org_net_amount', $orgNet);

        $palletDelivery->update($modelData);
    }
}
