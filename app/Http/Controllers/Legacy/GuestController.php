<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 20 Oct 2020 17:07:11 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Legacy\Traits\LegacyHelpers;
use App\Models\System\Guest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GuestController extends Controller {
    use LegacyHelpers;

    private $object_parameters;
    private $settings;
    private $data;
    private $legacy;


    public function __construct() {
        Guest::disableAuditing();
    }

    function sync(Request $request) {

        $request_data          = $request->all();

        $this->parseRequest($request_data);
        $this->object_parameters['data']     = $this->data;
        $this->object_parameters['settings'] = $this->settings;
        $this->object_parameters['tenant_id'] = app('currentTenant')->id;

        $guest = (new Guest())->updateOrCreate(
            [
                'legacy_id' => $request->legacy_id,
            ], $this->object_parameters
        );

        return response()->json($guest, 200);

    }

    function update($legacy_id,Request $request) {

        $this->parseRequest($request->all());
        if($guest = (new Guest)->firstWhere('legacy_id', $legacy_id)){
            $guest=$this->commonUpdate($guest);
            return response()->json($guest, 200);
        }else{
            return response()->json(['errors' => 'object not found'], 470);
        }
    }
}
