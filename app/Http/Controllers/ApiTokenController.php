<?php
/*
Author: Raul A Perusquía-Flores (raul@inikoo.com)
Created:  Sat Aug 08 2020 23:27:23 GMT+0800 (Malaysia Time) Kuala Lumpur, Malaysia
Copyright (c) 2020,  AIku.io

Version 4
*/


namespace App\Http\Controllers;

use App\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\User;
use App\Models\HR\ClockingMachine;


class ApiTokenController extends Controller {


    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(Request $request) {

        $request->validate(
            [
                'handle'       => 'required',
                'password'     => 'required',
                'role'         => 'required',
                'device_name'  => 'required',
                'on_duplicate' => 'sometimes|in:Steal,Force,ThrowError',
            ]
        );

        $user = (new User)->where('handle', $request->handle)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {

            throw ValidationException::withMessages(
                [
                    'email' => ['wrong-credentials'],
                ]
            );

        }

        switch ($request->role) {
            case 'clocking-machine':
                return $this->create_clocking_machine_token($user, $request);
            default:
                throw ValidationException::withMessages(
                    [
                        'role' => ['unknown-role'],
                    ]
                );


        }


    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function registerApp(Request $request) {

        $request->validate(
            [
                'code' => 'required|size:6',
            ]
        );

        throw ValidationException::withMessages(
            [
                'role' => ['unknown-role'],
            ]
        );


    }

    /**
     * @param $user
     * @param $request
     *
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    private function create_clocking_machine_token($user, $request) {

        $token_abilities = [
            'employee:view',
            'timesheet:create',
            'timesheet:view'
        ];

        if ($request->on_duplicate == null) {
            $request->on_duplicate = 'ThrowError';
        }

        $clocking_machine = (new ClockingMachine)->where('name', $request->device_name)->firstOr(
            function () use ($request, $user, $token_abilities) {

                $request->on_duplicate = '';

                $clocking_machine       = new ClockingMachine();
                $clocking_machine->name = $request->device_name;
                $clocking_machine->save();

                return $clocking_machine;

            }
        );


        switch ($request->on_duplicate) {
            case 'Steal':
                $user->tokens()->where('name', 'clocking-machine-'.$clocking_machine->slug)->delete();
                break;
            case 'Force':
                $clocking_machine       = new ClockingMachine();
                $clocking_machine->name = $request->device_name;
                $clocking_machine->save();
                break;
            case 'ThrowError':

                throw ValidationException::withMessages(
                    [
                        'device_name' => ['Duplicated device name.'],
                    ]
                );

        }

        $payload = [
            'clockingMachine' => $clocking_machine->toArray()
        ];

        return $this->create_token($user, 'clocking-machine-'.$clocking_machine->slug, $token_abilities, $payload);

    }

    private function create_token($user, $token_name, $token_abilities, $payload = []) {

        $payload['token'] = $user->createToken(
            $token_name, $token_abilities
        )->plainTextToken;

        return response()->json([$payload]);
    }
    function createAccessCode(Request $request) {

        $request->validate(
            [
                'handle'    => 'required',
                'subdomain' => 'required|exists:tenants',
            ]
        );



        $tenant= (new Tenant)->firstWhere('subdomain', $request->subdomain);


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

}
