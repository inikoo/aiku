<?php
/*
Author: Raul A Perusquía-Flores (raul@inikoo.com)
Created:  Mon Aug 03 2020 11:15:17 GMT+0800 (Malaysia Time) Kuala Lumpur, Malaysia
Copyright (c) 2020,  AIku.io

Version 4
*/

use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder {
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run() {

        factory(App\Tenant::class, 3)->create()->each(function ($tenant) {

        });

    }
}
