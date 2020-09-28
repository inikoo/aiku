<?php
/*
Author: Raul Perusquia (raul@inikoo.com)
Created:  Mon Jul 27 2020 18:25:03 GMT+0800 (Malaysia Time) Tioman, Malaysia
Copyright (c) 2020, AIku.io

Version 4
*/

include_once 'Relocator.php';

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Employee;

class EmployeeRelocator extends Relocator {
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run() {


        $tenant = app('currentTenant');

        $this->set_legacy_connection($tenant->data['legacy_database']);


        foreach (
            DB::connection('legacy')->select("select * from`Staff Dimension`", []) as $legacy_data
        ) {



            $employee_data = $this->fill_data(
                [
                    'personal_identification' => 'Staff Official ID',
                    'next_of_kind.name'       => 'Staff Next of Kind',
                    'date_of_birth'           => 'Staff Birthday'
                ], $legacy_data
            );


            Employee::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'slug'      => Str::kebab($legacy_data->{'Staff Name'}),
                    'name'      => $legacy_data->{'Staff Name'},
                ], [
                    'legacy_id' => $legacy_data->{'Staff Key'},
                    'status'    => ($legacy_data->{'Staff Currently Working'} == 'Yes' ? 'Working' : 'NotWorking'),
                    'data'      => $employee_data

                ]
            );

        }

    }


}
