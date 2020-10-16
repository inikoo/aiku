<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 16 Oct 2020 16:45:46 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Http\Controllers;

use App\Models\Distribution\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class StockController extends Controller {


    function update(Request $request) {


        Stock::disableAuditing();

        $new_obj_data = $request->all();


        $data = Arr::pull($new_obj_data, 'data', false);
        $data = ($data ? array_filter(json_decode($data, true)) : []);


        $new_obj_data['tenant_id'] = app('currentTenant')->id;

        $stock = (new Stock)->updateOrCreate(
            [
                'legacy_id' => $request->legacy_id,

            ], $new_obj_data
        );


        $data = $data + $stock->data;
        $data = array_filter($data);


        $stock->data = $data;

        $stock->save();

        return response()->json($stock, 200);


    }
}
