<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 19 Nov 2020 14:22:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use App\Models\Sales\Charge;
use App\Models\Stores\Store;

function get_legacy_charges_transaction_id($legacyTransactionKey) {
    if ($legacyTransactionKey) {
        $charge = (new Charge())->firstWhere('legacy_id', $legacyTransactionKey);
        if ($charge) {
            return $charge->id;
        }
    }

    return null;
}

function get_legacy_type_charges_transaction_id($type, $store_id) {
    /**
     * @var $charge Charge
     */
    $charge = (new Store)->find($store_id)->charges()->firstWhere('type', $type);
    if ($charge) {
        return $charge->id;
    }

    return null;
}
