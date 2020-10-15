<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 08 Oct 2020 22:38:34 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Http\Controllers;

use App\Models\CRM\Customer;
use App\Models\Stores\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CustomerController extends Controller {


    function update(Request $request) {


        Customer::disableAuditing();

        $new_obj_data = $request->all();


        $legacy                = Arr::pull($new_obj_data, 'legacy', false);
        $data                  = Arr::pull($new_obj_data, 'data', false);
        $settings              = Arr::pull($new_obj_data, 'settings', false);
        $tax_number_validation = Arr::pull($new_obj_data, 'tax_number_validation', false);


        $legacy                = ($legacy ? array_filter(json_decode($legacy, true)) : []);
        $data                  = ($data ? array_filter(json_decode($data, true)) : []);
        $settings              = ($settings ? array_filter(json_decode($settings, true)) : []);
        $tax_number_validation = ($tax_number_validation ? array_filter(json_decode($tax_number_validation, true)) : []);


        $store = (new Store)->firstWhere('legacy_id', $legacy['store_key']);

        $new_obj_data['tenant_id'] = app('currentTenant')->id;
        $new_obj_data['store_id']  = $store->id;

        $customer = (new Customer)->updateOrCreate(
            [
                'legacy_id' => $request->legacy_id,

            ], $new_obj_data
        );


        $data = $data + $customer->data;

        if (empty($tax_number_validation)) {
            unset($data['tax_number_validation']);
        } else {
            $tax_number_validation         = array_filter($tax_number_validation);
            $data['tax_number_validation'] = $tax_number_validation;
        }
        $data = array_filter($data);


        $customer->data     = $data;
        $customer->settings = $settings + $customer->settings;

        $customer->save();

        return response()->json($customer, 200);


    }
}
