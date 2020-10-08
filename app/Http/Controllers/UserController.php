<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 08 Oct 2020 01:13:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller {




    function update(Request $request) {


        User::disableAuditing();

        $new_obj_data              = $request->all();


        $data = json_decode(Arr::pull($new_obj_data, 'data', '[]'),true);
        $password = Arr::pull($new_obj_data, 'password', false);
        $pin = Arr::pull($new_obj_data, 'pin', false);

        $new_obj_data['tenant_id'] = app('currentTenant')->id;



        $user = (new User)->updateOrCreate(
            [
                'legacy_id' => $request->legacy_id,

            ], $new_obj_data
        );


        if($password){
            $user->password=bcrypt($password);
            $user->confidential=['pwd_legacy'=>$password]+$user->confidential;

        }
        if($pin){
            $user->pin=Hash::make($pin);
        }
        $user->data=array_merge($data,$user->data);
        $user->data=$data+$user->data;

        $user->save();
        return response()->json($user, 200);


    }
}
