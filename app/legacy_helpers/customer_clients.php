<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 20 Nov 2020 01:02:15 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use App\Models\CRM\CustomerClient;
use App\Models\Helpers\Address;
use Illuminate\Support\Facades\DB;

function relocate_customer_client($tenant, $customer) {

    $_table = '`Customer Client Dimension`';
    $_where = '`Customer Client Customer Key`';

    foreach (DB::connection('legacy')->select("select * from $_table where $_where=?", [$customer->legacy_id]) as $legacy_data) {


        $metadata   = json_decode($legacy_data->{'Customer Client Metadata'}, true);
        $deleted_at = null;
        if ($legacy_data->{'Customer Client Status'} == 'Inactive') {
            $deleted_at = $metadata['deactivated_date'];
        }

        $customer_client_data = fill_legacy_data(
            [
                'contact' => 'Customer Client Main Contact Name',
                'company' => 'Customer Client Company Name',
                'mobile'  => 'Customer Client Main Plain Mobile',
                'phone'   => 'Customer Client Main Plain Telephone',
                'email'   => 'Customer Client Main Plain Email',


            ], $legacy_data
        );

        $customerClient = CustomerClient::withTrashed()->updateOrCreate(
            [
                'legacy_id' => $legacy_data->{'Customer Client Key'},

            ], [
                'tenant_id'   => $tenant->id,
                'customer_id' => $customer->id,
                'code'        => $legacy_data->{'Customer Client Code'},
                'name'        => $legacy_data->{'Customer Client Name'},
                'data'        => $customer_client_data,
                'created_at'  => $legacy_data->{'Customer Client Creation Date'},
                'deleted_at'  => $deleted_at,


            ]
        );

        $oldAddressId = $customerClient->deliery_id;

        $delivery_address = process_instance_address_legacy('CustomerClient', $customerClient->id, 'Contact', $legacy_data);

        $customerClient->delivery_address_id = $delivery_address->id;
        $customerClient->save();
        if ($oldAddressId and $delivery_address->id != $oldAddressId) {
            $address = (new Address)->find($oldAddressId);
            if ($address) {
                $address->deleteIfOrphan();
            }
        }

        try {
            relocate_basket($legacy_data->{'Customer Client Key'}, $customerClient->basket);
        } catch (Exception $e) {
            //
        }

    }


}
