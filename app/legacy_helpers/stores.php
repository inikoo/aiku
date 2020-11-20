<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 20 Nov 2020 14:49:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */


use App\Models\Stores\Store;

function relocate_stores($tenant,$legacy_data) {


    $legacy_data->{'Store Can Collect'} = $legacy_data->{'Store Can Collect'} == 'Yes';


    $website_data = fill_legacy_data(
        [
            'url'      => 'Store URL',
            'email'    => 'Store Email',
            'currency' => 'Store Currency Code',
            'locale'   => 'Store Locale',
            'timezone' => 'Store Timezone',
            'type'     => 'Store Type'

        ], $legacy_data
    );

    $website_data['type'] = strtolower($website_data['type']);

    $website_settings = fill_legacy_data(
        [
            'can_collect' => 'Store Can Collect',

        ], $legacy_data
    );

    $legacy_status_to_state = [
        'InProcess'   => 'creating',
        'Normal'      => 'live',
        'ClosingDown' => 'closed',
        'Closed'      => 'closed'
    ];

    $state = $legacy_status_to_state[$legacy_data->{'Store Status'}];


    if ($legacy_data->{'Store Valid To'} == '0000-00-00 00:00:00') {
        $deleted_at = date('Y-m-d H:i:s');
    } else {
        $deleted_at = $legacy_data->{'Store Valid To'};
    }


    return Store::withTrashed()->updateOrCreate(
        [
            'legacy_id' => $legacy_data->{'Store Key'},

        ], [
            'tenant_id'  => $tenant->id,
            'code'       => $legacy_data->{'Store Code'},
            'name'       => $legacy_data->{'Store Name'},
            'state'      => $state,
            'data'       => $website_data,
            'settings'   => $website_settings,
            'created_at' => $legacy_data->{'Store Valid From'},
            'deleted_at' => ($state == 'closed' ? $deleted_at : null)
        ]
    );
}
