<?php
/*
Author: Raul Perusquia (raul@inikoo.com)
Created:  Tue Jul 28 2020 21:55:24 GMT+0800 (Malaysia Time) Tioman, Malaysia
Copyright (c) 2020, AIku.io

Version 4
*/


use Illuminate\Support\Facades\DB;
use App\Models\HR\Employee;
use App\User;


class UserRelocator extends Relocator {
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run() {

        $tenant = app('currentTenant');

        $this->set_legacy_connection($tenant->data['legacy_database']);

        foreach (DB::connection('legacy')->select("select * from`User Dimension`", []) as $legacy_data) {


            if ($legacy_data->{'User Type'} == 'Staff' or $legacy_data->{'User Type'} == 'Guest') {

                $user_parent_key = null;
                switch ($legacy_data->{'User Type'}) {
                    case 'Staff':
                        $user_parent = 'employee';
                        if ($employee = Employee::where('legacy_id', $legacy_data->{'User Parent Key'})->first()) {
                            $user_parent_key = $employee->id;
                        }
                        break;
                    case 'Guest':
                        $user_parent = 'contractor';
                        if ($employee = Employee::where('legacy_id', $legacy_data->{'User Parent Key'})->first()) {
                            $user_parent_key = $employee->id;
                        }
                        break;

                    default:
                        $user_parent = $legacy_data->{'User Type'};
                }

                $user_settings = [];

                $user_data = $this->fill_data(
                    [
                        'pwd_legacy' => 'User Password'
                    ], $legacy_data
                );


                User::firstOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'handle'    => $legacy_data->{'User Handle'},
                    ], [
                        'password'    => bcrypt($legacy_data->{'User Password'}),
                        'legacy_id'   => $legacy_data->{'User Key'},
                        'userable_type'    => $user_parent,
                        'userable_id' => $user_parent_key,
                        'status'      => ($legacy_data->{'User Active'} == 'Yes' ? 'Active' : 'Disabled'),
                        'settings'    => $user_settings,
                        'data'        => $user_data

                    ]
                );
            }
        }

    }
}
