<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 16 Oct 2020 14:08:58 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Http\Controllers;

use App\Models\CRM\Customer;
use App\Models\CRM\CustomerClient;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CustomerClientController extends Controller {


    function update(Request $request) {


        CustomerClient::disableAuditing();

        $new_obj_data = $request->all();


        $legacy                = Arr::pull($new_obj_data, 'legacy', false);
        $data                  = Arr::pull($new_obj_data, 'data', false);

        $legacy                = ($legacy ? array_filter(json_decode($legacy, true)) : []);
        $data                  = ($data ? array_filter(json_decode($data, true)) : []);


        $customer = (new Customer)->firstWhere('legacy_id', $legacy['customer_key']);
        if(!$customer){
            return response()->json(['errors' =>'Customer not found'], 422);
        }


        $new_obj_data['tenant_id'] = app('currentTenant')->id;
        $new_obj_data['customer_id']  = $customer->id;

        $customer_client = (new CustomerClient)->updateOrCreate(
            [
                'legacy_id' => $request->legacy_id,

            ], $new_obj_data
        );


        $data = $data + $customer_client->data;
        $data = array_filter($data);


        $customer_client->data     = $data;

        $customer_client->save();

        return response()->json($customer_client, 200);


    }
}
