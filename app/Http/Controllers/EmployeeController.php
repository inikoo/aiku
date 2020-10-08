<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Wed, 07 Oct 2020 15:36:43 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Http\Controllers;

use App\Models\HR\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class EmployeeController extends Controller {


    /*
    function update($legacy_id, Request $request) {

        $employee = (new Employee)->firstWhere('legacy_id', $legacy_id);
        if (!$employee) {
            return response()->json(['errors' => 'object not found'], 404);
        }

        $employee->fill($request->all());
        $employee->save();
    }
    */

    function update(Request $request) {


        Employee::disableAuditing();

        $new_obj_data              = $request->all();

        $data = json_decode(Arr::pull($new_obj_data, 'data', '[]'),true);

        $new_obj_data['tenant_id'] = app('currentTenant')->id;



        $employee = (new Employee)->updateOrCreate(
            [
                'legacy_id' => $request->legacy_id,

            ], $new_obj_data
        );

        $employee->data=$data+$employee->data;


        $employee->save();
        return response()->json($employee, 200);


    }
}
