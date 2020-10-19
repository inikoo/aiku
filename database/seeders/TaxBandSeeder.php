<?php

namespace Database\Seeders;

use App\Models\Helpers\Country;
use App\Models\Helpers\Tax;
use App\Models\Sales\TaxBand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class TaxBandSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        $tenant = app('currentTenant');
        if ($country = (new Country)->firstWhere('code', Arr::get($tenant->data, 'country_code'))) {


            $composite_base       = [];
            $composite_complement = [];

            foreach ((new Tax)->where('country_code', $country->code)->get() as $tax) {



                $code = $tax->code;

                $data    = [
                    'rate'  => round($tax->data['rate'],4),
                    'taxes' => [
                        $tax->id => [
                            'rate'        => round($tax->data['rate'],4),
                            'type'        => $tax->data['type']['code'],
                            'description' => $tax->data['type']['description']
                        ]

                    ],

                ];
                $taxBand = (new TaxBand)->updateOrCreate(
                    [
                        'code' => $code
                    ], [
                        'name'      => $tax->data['name'],
                        'data'      => $data,
                        'tenant_id' => $tenant->id,
                        'type'      => $tax->data['type']['code']


                    ]
                );

                if ($country->code == 'ES') {
                    if ($taxBand->type == 'iva') {
                        $composite_base[] = $taxBand->id;
                    }
                    if ($taxBand->type == 're') {
                        $composite_complement[] = $taxBand->id;
                    }
                }

            }

            foreach ($composite_base as $base_id) {
                $base = (new TaxBand)->find($base_id);

                foreach ($composite_complement as $complement_id) {
                    $complement = (new TaxBand)->find($complement_id);


                    $code = $base->code.'+'.$complement->code;

                    if(round($base->data['rate']+$complement->data['rate'],4)==0.262){
                        $code='s8';
                    }

                    $data    = [
                        'rate'  => round($base->data['rate']+$complement->data['rate'],4),
                        'taxes' => array_merge($base->data['taxes'],$complement->data['taxes'])



                    ];
                    (new TaxBand)->updateOrCreate(
                        [
                            'code' => $code
                        ], [
                            'name'      => $base->name.' + '.$complement->name,
                            'data'      => $data,
                            'tenant_id' => $tenant->id,
                            'type'      => $base->type.'+'.$complement->type


                        ]
                    );

                }

            }


        }


    }
}
