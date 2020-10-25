<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 29 Sep 2020 14:52:43 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Http\Controllers;

use App\Models\Helpers\AccessCode;
use App\Models\System\Device;
use App\Tenant;
use Illuminate\Http\Request;
use App\User;


class AppAuthController extends Controller {


    function createAccessCode(Request $request) {

        $request->validate(
            [
                'handle'    => 'required',
                'subdomain' => 'required|exists:tenants',
            ]
        );



        $tenant= (new Tenant)->firstWhere('slug', $request->subdomain);


        $tenant->makeCurrent();

        $request->validate(
            [
                'user_id' => 'required|exists:users,id',
            ]
        );

        $user = (new User)->find($request->user_id);

        $accessCode = $user->createAccessCode();

        return response()->json(
            [
                'code' => $accessCode->code
            ]
        );


    }

    function loginWithAccessCode(Request $request) {

        $request->validate(
            [
                'code'    => 'required|exists:access_codes',
                'app' => 'required',
                'device_id' => 'required',
                'device_tag' => 'required',
            ]
        );

        $accessCode= (new AccessCode)->firstWhere('code', $request->code);

        $tenant= (new Tenant)->find($accessCode->scope_id);
        $tenant->makeCurrent();

        $user= (new User)->find($accessCode->payload['user_id']);

        $tokenData= preg_split('/\|/',$user->createToken($request->device_id)->plainTextToken);

        $device = (new Device)->updateOrCreate(
            [
                'app' => $request->app,
                'uid' => $request->device_id,
                'user_id' => $user->id,
                'tenant_id' => $tenant->id
            ],
            [
                'tag'=>$request->device_id,
                'personal_access_token_id'=>$tokenData[0]

            ]
        );


        $payload=[
            'apiUrl'     => config('app.protocol').'://'.$tenant->slug.'.'.config('app.domain'),
            'token'=>$tokenData[1],
            'userData'=>[
                'tokenId'=>$device->personal_access_token_id,
                'id'=>$user->id,
                'handle'=>$user->handle,
                'name'=>$user->userable->name,
            ],
            'tenantData'=>[
                'code'=>$tenant->slug,
                'name'=>$tenant->name,
            ],
            'deviceId'=>$device->id

        ];

        return response()->json($payload);








    }

}
