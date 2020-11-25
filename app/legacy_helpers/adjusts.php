<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 19 Nov 2020 14:26:46 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */


use App\Models\Sales\Adjust;

use App\Models\Stores\Store;


function get_legacy_type_adjusts_transaction_id($type, $store_id,$onptf_data) {
    /**
     * @var $adjust Adjust
     */

    $store=Store::withTrashed()->find($store_id);
    if(!$store){
        print_r($onptf_data);
    }
    $adjust = $store->adjusts()->firstWhere('type', $type);
    if ($adjust) {
        return $adjust->id;
    }

    return null;
}
