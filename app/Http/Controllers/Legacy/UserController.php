<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 20 Oct 2020 17:35:31 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Legacy\Traits\LegacyHelpers;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class UserController extends Controller {
    use LegacyHelpers;

    private $object_parameters;
    private $settings;

    private $data;

    public function __construct() {
        User::disableAuditing();
    }

    function sync(Request $request) {

        $request_data = $request->all();

        $confidential       = Arr::pull($request_data, 'confidential', false);
        $confidential = ($confidential ? array_filter(json_decode($confidential, true)) : []);


        $this->parseRequest($request_data);
        $this->object_parameters['data']         = $this->data;
        $this->object_parameters['settings']     = $this->settings;
        $this->object_parameters['confidential'] = $confidential;

        $this->object_parameters['tenant_id'] = app('currentTenant')->id;

        $this->object_parameters['pin']      = Hash::make($this->object_parameters['pin']);
        $this->object_parameters['password'] = bcrypt($this->object_parameters['password']);


        $user = (new User())->updateOrCreate(
            [
                'legacy_id' => $request->legacy_id,
            ], $this->object_parameters
        );

        return response()->json($user, 200);

    }


    function update($legacy_id, Request $request) {


        $this->parseRequest($request->all());
        $user = (new User)->firstWhere('legacy_id', $legacy_id);
        if ($user) {

            if (isset($this->object_parameters['password'])) {
                $user->confidential = ['pwd_legacy' => $this->object_parameters['password']] + $user->confidential;

                $this->object_parameters['password'] = bcrypt($this->object_parameters['password']);

            }

            if (isset($this->object_parameters['pin'])) {

                $this->object_parameters['pin'] = Hash::make($this->object_parameters['pin']);

            }

            $user = $this->commonUpdate($user);

            return response()->json($user, 200);

        } else {
            return response()->json(['errors' => 'object not found'], 470);
        }





    }
}
