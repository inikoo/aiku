<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 20 Nov 2020 00:47:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */


use App\Models\CRM\Customer;
use App\Models\CRM\Prospect;
use App\Models\HR\ProspectSalesRepresentative;
use App\Models\Stores\Store;
use App\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


function relocate_prospect($tenant, $legacy_data) {


    $prospect_data = fill_legacy_data(
        [

            'mobile'                     => 'Prospect Main Plain Mobile',
            'phone'                      => 'Prospect Main Plain Telephone',
            'contact'                    => 'Prospect Main Contact Name',
            'company'                    => 'Prospect Company Name',
            'website'                    => 'Prospect Website',
            'note'                       => 'Prospect Sticky Note',
            'timeline.contacted_at'      => 'Prospect First Contacted Date',
            'timeline.last_contacted_at' => 'Prospect Last Contacted Date',
            'timeline.registered_at'     => 'Prospect Registration Date',
            'timeline.invoiced_at'       => 'Prospect Invoiced Date',
            'timeline.lost_at'           => 'Prospect Lost Date',
        ], $legacy_data
    );


    foreach (Arr::get($prospect_data, 'timeline', []) as $key => $value) {
        if ($value == '0000-00-00 00:00:00') {
            unset($prospect_data['timeline'][$key]);
        }
    }


    $prospect_settings = [];


    $state = Str::snake($legacy_data->{'Prospect Status'});


    $store       = Store::withTrashed()->firstWhere('legacy_id', $legacy_data->{'Prospect Store Key'});
    $customer_id = null;
    if ($legacy_data->{'Prospect Customer Key'}) {
        $customer = Customer::withTrashed()->firstWhere('legacy_id', $legacy_data->{'Prospect Customer Key'});
        if ($customer) {
            $customer_id = $customer->id;
        }
    }


    $prospect = (new Prospect)->updateOrCreate(
        [
            'legacy_id' => $legacy_data->{'Prospect Key'},

        ], [
            'tenant_id'   => $tenant->id,
            'name'        => $legacy_data->{'Prospect Name'},
            'email'       => $legacy_data->{'Prospect Main Plain Email'},
            'state'       => $state,
            'data'        => $prospect_data,
            'settings'    => $prospect_settings,
            'created_at'  => $legacy_data->{'Prospect Created Date'},
            'store_id'    => $store->id,
            'customer_id' => $customer_id

        ]
    );


    $contact_address = process_instance_address_legacy('Prospect', $prospect->id, 'Contact', $legacy_data);

    if ($contact_address != null) {
        $prospect->contact_address_id = $contact_address->id;
        $prospect->save();
    }

    if ($legacy_data->{'Prospect Sales Representative Key'}) {

        $sql = "`Sales Representative User Key` from `Sales Representative Dimension` where `Sales Representative Key`=? ";
        foreach (DB::connection('legacy')->select("select $sql", [$legacy_data->{'Prospect Sales Representative Key'}]) as $sales_rep_data) {
            $user = (new User)->firstWhere('legacy_id', $sales_rep_data->{'Sales Representative User Key'});
            if ($user) {


                (new ProspectSalesRepresentative)->updateOrCreate(
                    [
                        'prospect_id'               => $prospect->id,
                        'sales_representative_id'   => $user->userable_id,
                        'sales_representative_type' => $user->userable_type,

                    ], [

                        'allocation' => 1
                    ]
                );

            }
        }


    }

    return $prospect;


}
