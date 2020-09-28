<?php
/*
Author: Amila

Copyright (c) 2020, AIku.io

Version 4
*/

use Illuminate\Database\Seeder;


class WarehouseSeeder extends Seeder {
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run() {

        $tenant = app('currentTenant');
        factory(App\Models\Distribution\Warehouse::class, rand(2, 3))->create(
            [
                'tenant_id' => $tenant->id,
            ]
        )->each(
            function ($warehouse) {


                $warehouse->areas()->saveMany(
                    factory(App\Models\Distribution\WarehouseArea::class, 5)->make(
                        [
                            'tenant_id' => $warehouse->tenant_id
                        ]
                    )
                )->each(
                    function ($area) {


                        $area->locations()->saveMany(
                            factory(App\Models\Distribution\Location::class, 10)->make(
                                [
                                    'tenant_id' => $area->tenant_id,
                                    'warehouse_id' => $area->warehouse_id
                                ]
                            )
                        );

                    }
                );


            }


        );


    }


}
