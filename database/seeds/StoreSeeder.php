<?php

/*
Author: Amila

Copyright (c) 2020, AIku.io

Version 4
*/

use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tenant = app('currentTenant');
<<<<<<< Updated upstream
=======

>>>>>>> Stashed changes
        factory(App\Store::class, rand(2, 3))->create(
            [
                'tenant_id' => $tenant->id,
            ]
        )->each(
            function ($store) {

<<<<<<< Updated upstream
                $store->customers()->saveMany(
                    factory(App\Customer::class, 5)->make(
=======

                $store->prospects()->saveMany(
                    factory(App\Prospect::class, 5)->make(
>>>>>>> Stashed changes
                        [
                            'tenant_id' => $store->tenant_id
                        ]
                    )
<<<<<<< Updated upstream
                )->each(
                    function ($customer) {


                        $customer->orders()->saveMany(
                            factory(App\Order::class, 10)->make(
                                [
                                    'tenant_id' => $customer->tenant_id
                                ]
                            )
                        );

                    }
                )->each(
                    function ($orders) {


                        $orders->invoice()->save(
                            factory(App\Order::class, 10)->make(
                                [
                                    'tenant_id' => $orders->tenant_id
                                ]
                            )
                        );


                        $orders->deliveynotes()->save(
                            factory(App\DeliveryNote::class, 10)->make(
                                [
                                    'tenant_id' => $orders->tenant_id
                                ]
                            )
                        );

                    }
                )->each(
                    function ($products) {


                        $products->invoice()->saveMany(
                            factory(App\Order::class, 10)->make(
                                [
                                    'tenant_id' => $products->tenant_id
                                ]
                            )
                        );

                        

                    }

                    
                );

            }


        );
    }
=======
                );


            }


        );


    }


    
>>>>>>> Stashed changes
}
