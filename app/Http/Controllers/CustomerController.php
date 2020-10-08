<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 08 Oct 2020 22:38:34 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Http\Controllers;

use App\Models\CRM\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CustomerController extends Controller {


    function update(Request $request) {


        Customer::disableAuditing();

        $new_obj_data              = $request->all();

        $data = json_decode(Arr::pull($new_obj_data, 'data', '[]'),true);
        $settings = json_decode(Arr::pull($new_obj_data, 'settings', '[]'),true);
        $tax_number_validation = json_decode(Arr::pull($new_obj_data, 'tax_number_validation', '[]'),true);
        $tax_number_validation = array_filter($tax_number_validation);




        $new_obj_data['tenant_id'] = app('currentTenant')->id;



        $store = (new Customer)->updateOrCreate(
            [
                'legacy_id' => $request->legacy_id,

            ], $new_obj_data
        );



        $data=$data+$store->data;

        if(empty($tax_number_validation)){
            unset($data['tax_number_validation']);
        }else{
            $data['tax_number_validation']=$tax_number_validation;
        }
        $data = array_filter($data);


        $store->data=$data;
        $store->settings=$settings+$store->settings;

        $store->save();
        return response()->json($store, 200);


    }
}
