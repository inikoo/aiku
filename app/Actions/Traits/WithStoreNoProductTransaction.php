<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 04 Sept 2024 20:31:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Traits;

use App\Models\Catalogue\Charge;
use App\Models\Ordering\Adjustment;
use App\Models\Ordering\ShippingZone;
use Illuminate\Support\Arr;

trait WithStoreNoProductTransaction
{
    private function prepareChargeTransaction($modelData): array
    {
        data_set($modelData, 'model_type', 'Charge');
        data_set($modelData, 'model_id', Arr::get($modelData, 'charge_id'));

        if(Arr::get($modelData, 'charge_id')) {
            data_set($modelData, 'asset_id', Arr::get($modelData, 'charge_id'));
            $charge = Charge::find(Arr::get($modelData, 'charge_id'));
            data_set($modelData, 'historic_asset_id', $charge->current_historic_asset_id);
        }

        data_forget($modelData, 'charge_id');
        return $modelData;
    }

    private function prepareShippingTransaction($modelData): array
    {
        data_set($modelData, 'model_type', 'ShippingZone');
        data_set($modelData, 'model_id', Arr::get($modelData, 'shipping_zone_id'));

        if(Arr::get($modelData, 'shipping_zone_id')) {
            data_set($modelData, 'asset_id', Arr::get($modelData, 'shipping_zone_id'));
            $shippingZone = ShippingZone::find(Arr::get($modelData, 'shipping_zone_id'));
            data_set($modelData, 'historic_asset_id', $shippingZone->current_historic_asset_id);
        }
        data_forget($modelData, 'shipping_zone_id');
        return $modelData;
    }

    private function prepareAdjustmentTransaction(Adjustment $adjustment, $modelData): array
    {
        data_set($modelData, 'model_type', 'Adjustment');
        data_set($modelData, 'model_id', $adjustment->id);

        $net   = $adjustment->net_amount;
        $gross = $net;

        data_set($modelData, 'gross_amount', $gross, overwrite: false);
        data_set($modelData, 'net_amount', $net, overwrite: false);
        return $modelData;

    }

}
