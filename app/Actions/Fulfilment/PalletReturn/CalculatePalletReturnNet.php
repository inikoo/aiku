<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 05 Jul 2024 00:03:50 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn;

use App\Actions\OrgAction;
use App\Enums\Fulfilment\FulfilmentTransaction\FulfilmentTransactionTypeEnum;
use App\Models\Fulfilment\PalletReturn;

class CalculatePalletReturnNet extends OrgAction
{
    public function handle(PalletReturn $palletReturn)
    {
        $physicalGoods    = $palletReturn->transactions()->where('type', FulfilmentTransactionTypeEnum::PRODUCT)->get();
        $physicalGoodsNet = $physicalGoods->sum('net_amount');
        $services         = $palletReturn->transactions()->where('type', FulfilmentTransactionTypeEnum::SERVICE)->get();
        $servicesNet      = $services->sum('net_amount');

        $tax = $palletReturn->taxCategory->rate;

        $net         = $physicalGoodsNet + $servicesNet;
        $taxAmount   = $net * $tax;
        $totalAmount = $net + $taxAmount;
        $grpNet      = $net * $palletReturn->grp_exchange;
        $orgNet      = $net * $palletReturn->org_exchange;

        data_set($modelData, 'net_amount', $net);
        data_set($modelData, 'total_amount', $totalAmount);
        data_set($modelData, 'tax_amount', $taxAmount);
        data_set($modelData, 'services_amount', $servicesNet);
        data_set($modelData, 'goods_amount', $physicalGoodsNet);
        data_set($modelData, 'grp_net_amount', $grpNet);
        data_set($modelData, 'org_net_amount', $orgNet);

        $palletReturn->update($modelData);
    }
}
