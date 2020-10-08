<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 08 Oct 2020 15:20:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Http\Controllers;

use App\Models\Stores\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class StoreController extends Controller {


    function update(Request $request) {


        Store::disableAuditing();

        $new_obj_data              = $request->all();

        $data = json_decode(Arr::pull($new_obj_data, 'data', '[]'),true);
        $settings = json_decode(Arr::pull($new_obj_data, 'settings', '[]'),true);

        $new_obj_data['tenant_id'] = app('currentTenant')->id;



        $store = (new Store)->updateOrCreate(
            [
                'legacy_id' => $request->legacy_id,

            ], $new_obj_data
        );



        $store->data=$data+$store->data;
        $store->settings=$settings+$store->settings;

        $store->save();
        return response()->json($store, 200);


    }
}
