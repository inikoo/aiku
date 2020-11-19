<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 19 Nov 2020 14:25:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */


use App\Models\Sales\ShippingZone;



function get_legacy_shipping_transaction_id($legacyTransactionKey) {
    if ($legacyTransactionKey) {
        $shipping_zone = (new ShippingZone())->firstWhere('legacy_id', $legacyTransactionKey);
        if ($shipping_zone) {
            return $shipping_zone->id;
        }
    }

    return null;
}
