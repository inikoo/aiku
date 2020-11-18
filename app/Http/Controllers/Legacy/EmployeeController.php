<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 20 Oct 2020 16:52:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Legacy\Traits\LegacyHelpers;
use App\Models\HR\Employee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EmployeeController extends Controller {
    use LegacyHelpers;

    private $object_parameters;
    private $settings;
    private $data;
    private $legacy;


    public function __construct() {
        Employee::disableAuditing();
    }

    function sync(Request $request) {

        $request_data          = $request->all();

        $this->parseRequest($request_data);
        $this->object_parameters['data']     = $this->data;
        $this->object_parameters['settings'] = $this->settings;
        $this->object_parameters['tenant_id'] = app('currentTenant')->id;

        $employee = (new Employee())->updateOrCreate(
            [
                'legacy_id' => $request->legacy_id,
            ], $this->object_parameters
        );

        return response()->json($employee, 200);

    }

    function update($legacy_id,Request $request) {

        $this->parseRequest($request->all());
        $employee = (new Employee)->firstWhere('legacy_id', $legacy_id);
        if($employee){
            $employee=$this->commonUpdate($employee);
            return response()->json($employee, 200);
        }else{
            return response()->json(['errors' => 'object not found'], 470);
        }
    }
}
