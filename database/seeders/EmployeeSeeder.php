<?php
/*
Author: Raul Perusquia (raul@inikoo.com)
Created:  Mon Jul 27 2020 18:25:03 GMT+0800 (Malaysia Time) Tioman, Malaysia
Copyright (c) 2020, AIku.io

Version 4
*/

use Illuminate\Database\Seeder;


class EmployeeSeeder extends Seeder {
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run() {


        $tenant = app('currentTenant');


        factory(App\Models\HR\Employee::class, rand(10, 20))->create(
            [
                'tenant_id' => $tenant->id,
            ]
        )->each(
            function ($employee) {

                if($employee->id==1){
                    $employee->user->assignRole('super-system-admin');
                }elseif($employee->id==2){
                    $employee->user->assignRole('system-admin');
                }elseif($employee->id==3){
                    $employee->user->assignRole('human-resources-admin');
                }elseif($employee->id==4){
                    $employee->user->assignRole('human-resources-clerk');
                }


            }
        );

    }


}
