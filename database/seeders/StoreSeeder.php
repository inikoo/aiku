<?php

/*
Author: Amila

Copyright (c) 2020, AIku.io

Version 4
*/

use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder {
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run() {
        $tenant = app('currentTenant');

        factory(App\Models\Stores\Store::class, rand(2, 3))->create(
            [
                'tenant_id' => $tenant->id,
            ]
        )->each(
            function ($store) {


                $store->prospects()->saveMany(
                    factory(App\Models\CRM\Prospect::class, 5)->make(
                        [
                            'tenant_id' => $store->tenant_id
                        ]
                    )
                );

                $store->products()->saveMany(
                    factory(App\Models\Stores\Product::class, 5)->make(
                        [
                            'tenant_id' => $store->tenant_id
                        ]
                    )
                )->each(
                    function ($product) {
                        $product->parts()->saveMany(
                            factory(App\Models\Distribution\Stock::class, 1)->make(
                                [
                                    'tenant_id' => $product->tenant_id
                                ]
                            )
                        );

                    }
                );


                $store->charges()->saveMany(
                    factory(App\Models\Sales\Charge::class, 5)->make(
                        [
                            'tenant_id' => $store->tenant_id
                        ]
                    )
                );


                $store->customers()->saveMany(
                    factory(App\Models\CRM\Customer::class, 100)->make(
                        [
                            'tenant_id' => $store->tenant_id
                        ]
                    )
                );


                $store->websites()->saveMany(
                    factory(App\Models\ECommerce\Website::class, 1)->make(
                        [
                            'tenant_id' => $store->tenant_id
                        ]
                    )
                )->each(
                    function ($website) {


                        $website->webpages()->saveMany(
                            factory(App\Models\ECommerce\Webpage::class, 10)->make(
                                [
                                    'tenant_id' => $website->tenant_id
                                ]
                            )
                        )->each(
                            function ($webpage) {


                                $webpage->web_blocks()->saveMany(
                                    factory(App\Models\ECommerce\WebBlock::class, 10)->make(
                                        [
                                            'tenant_id' => $webpage->tenant_id
                                        ]
                                    )
                                );

                            }
                        );


                        $website->web_users()->saveMany(
                            factory(App\Models\ECommerce\WebUser::class, 10)->make(
                                [
                                    'tenant_id' => $website->tenant_id
                                ]
                            )
                        );


                    }
                );


                $store->orders()->saveMany(
                    factory(App\Models\Sales\Order::class, 5)->make(
                        [
                            'tenant_id' => $store->tenant_id
                        ]
                    )
                )->each(
                    function ($order) {


                        $order->invoice()->saveMany(
                            factory(App\Models\Sales\Invoice::class, 1)->make(
                                [
                                    'tenant_id' => $order->tenant_id
                                ]
                            )
                        );

                        $order->delivery_notes()->saveMany(
                            factory(App\Models\Distribution\DeliveryNote::class, 5)->make(
                                [
                                    'tenant_id' => $order->tenant_id
                                ]
                            )
                        );

                    }
                );


            }


        );


    }


}
