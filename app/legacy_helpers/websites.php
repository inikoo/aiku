<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 20 Nov 2020 14:57:42 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */


use App\Models\ECommerce\Website;
use App\Models\Stores\Store;

function relocate_websites($tenant, $legacy_data) {


    $website_data = fill_legacy_data(
        [
            'locale' => 'Website Locale',


        ], $legacy_data
    );

    $website_settings       = fill_legacy_data(
        [
            'google_tag'   => 'Website Google Tag Manager Code',
            'zendesk_chat' => 'Website Zendesk Chat Code',


        ], $legacy_data
    );
    $legacy_status_to_state = [
        'InProcess'   => 'creating',
        'Active'      => 'live',
        'Maintenance' => 'maintenance',
        'Closed'      => 'closed'
    ];


    $state = $legacy_status_to_state[$legacy_data->{'Website Status'}];


    if ($legacy_data->{'Website From'} == '0000-00-00 00:00:00') {
        $created_at = null;
    } else {
        $created_at = $legacy_data->{'Website From'};
    }

    if ($legacy_data->{'Website Launched'} == '0000-00-00 00:00:00' or $legacy_data->{'Website Launched'} == '') {
        $launched_at = null;
    } else {
        $launched_at = $legacy_data->{'Website Launched'};
    }


    $store = Store::withTrashed()->firstWhere('legacy_id', $legacy_data->{'Website Store Key'});


    return Website::withTrashed()->updateOrCreate(

        [
            'legacy_id' => $legacy_data->{'Website Key'},

        ], [

            'url'       => $legacy_data->{'Website URL'},
            'tenant_id' => $tenant->id,
            'store_id'  => $store->id,

            'name'        => $legacy_data->{'Website Name'},
            'state'       => $state,
            'data'        => $website_data,
            'settings'    => $website_settings,
            'created_at'  => $created_at,
            'launched_at' => $launched_at,
            'deleted_at'  => ($state == 'closed' ? gmdate('Y-m-d H:i:s') : null)
        ]
    );
}
