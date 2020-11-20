<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 19 Nov 2020 14:25:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */


use App\Models\Sales\ShippingSchema;
use App\Models\Sales\ShippingZone;
use App\Models\Stores\Store;
use Illuminate\Support\Facades\DB;


function get_legacy_shipping_transaction_id($legacyTransactionKey) {
    if ($legacyTransactionKey) {
        $shipping_zone = (new ShippingZone())->firstWhere('legacy_id', $legacyTransactionKey);
        if ($shipping_zone) {
            return $shipping_zone->id;
        }
    }

    return null;
}

function relocate_shipping_schemas($legacy_data) {


    $store = Store::withTrashed()->firstWhere('legacy_id', $legacy_data->{'Shipping Zone Schema Store Key'});


    $shipping_schema_data = fill_legacy_data(
        [
            'type' => 'Shipping Zone Schema Type',
        ], $legacy_data, 'strtolower'
    );


    $shipping_schema_settings = fill_legacy_data(
        [], $legacy_data
    );


    $shipping_schema = (new ShippingSchema())->updateOrCreate(
        [
            'legacy_id' => $legacy_data->{'Shipping Zone Schema Key'},

        ], [
            'tenant_id'  => $store->tenant_id,
            'store_id'   => $store->id,
            'status'     => $legacy_data->{'Shipping Zone Schema Store State'} == 'Active',
            'name'       => $legacy_data->{'Shipping Zone Schema Label'},
            'data'       => $shipping_schema_data,
            'settings'   => $shipping_schema_settings,
            'created_at' => $legacy_data->{'Shipping Zone Schema Creation Date'},
        ]
    );

    $shipping_zones_table = '`Shipping Zone Dimension`';
    $_where               = '`Shipping Zone Shipping Zone Schema Key`';
    foreach (DB::connection('legacy')->select("select * from  $shipping_zones_table where  $_where=?", [$legacy_data->{'Shipping Zone Schema Key'}]) as $legacy_shipping_zone_data) {


        $shipping_schema_data = fill_legacy_data(
            [
                'name' => 'Shipping Zone Name',
            ], $legacy_shipping_zone_data
        );


        $price_data = json_decode($legacy_shipping_zone_data->{'Shipping Zone Price'}, true);

        if ($price_data['type'] == 'TBC') {
            $shipping_schema_settings['price'] = [
                'type'   => 'tbp',
                'metric' => '',
                'rules'  => ''
            ];
        } else {
            $shipping_schema_settings['price'] = [
                'type'   => 'steps',
                'metric' => 'itemsNet',
                'rules'  => $price_data['steps']
            ];
        }


        $shipping_schema_settings['territories'] = json_decode($legacy_shipping_zone_data->{'Shipping Zone Territories'});

        (new ShippingZone())->updateOrCreate(
            [
                'legacy_id' => $legacy_shipping_zone_data->{'Shipping Zone Key'},

            ], [
                'tenant_id'          => $store->tenant_id,
                'shipping_schema_id' => $shipping_schema->id,
                'status'             => $legacy_shipping_zone_data->{'Shipping Zone Active'} == 'Yes',
                'code'               => $legacy_shipping_zone_data->{'Shipping Zone Code'},
                'data'               => $shipping_schema_data,
                'settings'           => $shipping_schema_settings,
                'created_at'         => $legacy_shipping_zone_data->{'Shipping Zone Creation Date'},
                'precedence'         => $legacy_shipping_zone_data->{'Shipping Zone Position'}
            ]
        );

    }

    return $shipping_schema;


}
