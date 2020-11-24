<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 20 Nov 2020 15:05:15 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */


use App\Models\Notifications\EmailService;
use App\Models\Stores\Store;
use Illuminate\Support\Str;

function relocate_email_services($tenant,$legacy_data) {


    $store = Store::withTrashed()->firstWhere('legacy_id', $legacy_data->{'Email Campaign Type Store Key'});


    $email_service_data = fill_legacy_data(
        [


        ], $legacy_data, 'snake'
    );


    $email_service_settings = fill_legacy_data(
        [], $legacy_data
    );

    $state = Str::snake($legacy_data->{'Email Campaign Type Status'});


    return (new EmailService())->updateOrCreate(
        [
            'legacy_id' => $legacy_data->{'Email Campaign Type Key'},

        ], [
            'tenant_id'      => $tenant->id,
            'type'           => Str::snake(strtolower($legacy_data->{'Email Campaign Type Scope'})),
            'subtype'        => Str::snake(strtolower($legacy_data->{'Email Campaign Type Code'})),
            'container_type' => 'Store',
            'container_id'   => $store->id,
            'status'         => $legacy_data->{'Email Campaign Type Status'} == 'Active',
            'state'          => $state,
            'data'           => $email_service_data,
            'settings'       => $email_service_settings,
        ]
    );


}
