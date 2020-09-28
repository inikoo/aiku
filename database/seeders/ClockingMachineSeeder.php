<?php
/*
Author: Raul Perusquia (raul@inikoo.com)
Created:  Mon Jul 27 2020 18:25:03 GMT+0800 (Malaysia Time) Tioman, Malaysia
Copyright (c) 2020, AIku.io

Version 4
*/

use App\Models\HR\ClockingMachine;
use Illuminate\Database\Seeder;


class ClockingMachineSeeder extends Seeder {
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run() {


        $clockingMachine = new ClockingMachine();

        $clockingMachine->name = 'Office';

        $clockingMachine->save();

        $clockingMachine = new ClockingMachine();

        $clockingMachine->name = 'Warehouse';

        $clockingMachine->save();

    }


}
