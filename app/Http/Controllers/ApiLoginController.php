<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;


class ApiLoginController extends Controller {


    /**
     * Handle an authentication attempt.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(Request $request) {


        $credentials = $request->only('handle', 'password');

        data_set($credentials, 'status', 'active');

        $credentials['password'] = hash('sha256', $credentials['password']);

        if (Auth::attempt($credentials)) {

            /**
             * @var $user User
             */
            $user = Auth::user();
            $this->logAttempt($credentials['handle'], $user->id, $request->ip(), 'login');

            $user->last_login_at = gmdate('Y-m-d H:i:s+00');

            $user->save();

            return response()->json(
                [
                    'login'       => 'ok',
                    'user'        => $user,
                    'permissions' => $user->getAllPermissions(),
                    'userable'    => $user->userable
                ]
            );
        } else {
            $this->logAttempt($credentials['handle'], null, $request->ip(), 'loginFail');

            throw ValidationException::withMessages(
                [
                    'handle' => ['The provided credentials are incorrect.'],
                ]
            );
        }
    }

    private function logAttempt($handle, $user_id, $ip, $action) {


        if ($action == 'loginFail') {
            $user = (new User)->firstWhere('handle', $handle);
            if ($user !== null) {
                $user->last_login_fail_at = gmdate('Y-m-d H:i:s+00');
                $user_id                  = $user->id;
                $user->save();
            }
        }

        DB::insert(
            'insert into user_auth_logs (time, handle,user_id,ip,action) values (current_timestamp, ?,?,?,?)', [
                                                                                                                 $handle,
                                                                                                                 $user_id,
                                                                                                                 $ip,
                                                                                                                 $action
                                                                                                             ]
        );

    }


}
