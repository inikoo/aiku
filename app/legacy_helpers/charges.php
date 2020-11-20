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

function relocate_charges($legacy_data) {

    $store = Store::withTrashed()->firstWhere('legacy_id', $legacy_data->{'Charge Store Key'});
    if ($store) {


        $charge_data = fill_legacy_data(
            [
                'description'        => 'Charge Description',
                'public_description' => 'Charge Public Description',


            ], $legacy_data
        );


        $charge_settings = fill_legacy_data(
            [
                'amount' => 'Charge Metadata',


            ], $legacy_data
        );


        if ($legacy_data->{'Charge Terms Type'} == 'Order Items Net Amount') {
            $where_field = 'itemsNet';
        } else {
            $where_field = '';
        }


        if ($legacy_data->{'Charge Terms Metadata'} != '') {


            $where_metadata = preg_split('/;/', $legacy_data->{'Charge Terms Metadata'});


            $charge_settings['where'] = [
                $where_field,
                $where_metadata[0],
                $where_metadata[1]
            ];
        }


        return (new Charge)->updateOrCreate(
            [
                'legacy_id' => $legacy_data->{'Charge Key'},

            ], [
                'tenant_id'  => $store->tenant_id,
                'store_id'   => $store->id,
                'type'       => strtolower($legacy_data->{'Charge Scope'}),
                'status'     => $legacy_data->{'Charge Active'} == 'Yes',
                'name'       => $legacy_data->{'Charge Name'},
                'data'       => $charge_data,
                'settings'   => $charge_settings,
                'created_at' => $legacy_data->{'Charge Begin Date'},
            ]
        );
    } else {
        return false;
    }
}
