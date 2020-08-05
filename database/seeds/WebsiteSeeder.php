<?php

use Illuminate\Database\Seeder;

class WebsiteSeeder extends Seeder
{
      /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        $tenant = app('currentTenant');
        factory(App\Website::class, rand(2, 3))->create(
            [
                'tenant_id' => $tenant->id,
            ]
        )->each(
            function ($website) {


                $website->webpage()->saveMany(
                    factory(App\Webpage::class, 5)->make(
                        [
                            'tenant_id' => $website->tenant_id
                        ]
                    )
                );


            }


        );


    }


}
