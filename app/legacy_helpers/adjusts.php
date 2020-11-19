<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 19 Nov 2020 14:26:46 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */


use App\Models\Sales\Adjust;

use App\Models\Stores\Store;


function get_legacy_type_adjusts_transaction_id($type, $store_id) {
    /**
     * @var $adjust Adjust
     */
    $adjust = (new Store)->find($store_id)->adjusts()->firstWhere('type', $type);
    if ($adjust) {
        return $adjust->id;
    }

    return null;
}
