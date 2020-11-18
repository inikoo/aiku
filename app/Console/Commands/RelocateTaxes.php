<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 27 Aug 2020 14:16:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Console\Commands;

use App\Console\Commands\Traits\LegacyDataMigration;
use App\Models\Helpers\Tax;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RelocateTaxes extends Command {

    use LegacyDataMigration;

    protected $signature = 'relocate:taxes';

    protected $description = 'Migrate legacy taxes';

    public function handle() {


        DB::connection('mysql');

        $table = '`Tax Category Dimension`';

        foreach (DB::connection('mysql')->select('select * from '.$table, []) as $legacy_data) {


            if ($legacy_data->{'Composite'} == 'Yes') {
                continue;
            }


            $tax_data = fill_legacy_data(
                [
                    'type.code'        => 'Tax Category Type',
                    'type.description' => 'Tax Category Type Name',
                    'rate'             => 'Tax Category Rate',
                    'name'             => 'Tax Category Name'
                ], $legacy_data
            );

            $tax_data['type']['code'] = strtolower($tax_data['type']['code']);

            $tax_data['rate'] = round($tax_data['rate'], 4);

            if ($tax_data['type']['code'] == 'unknown') {
                $tax_data['rate'] = 0;
            }

            $country_translations = [
                'ESP' => 'ES',
                'GBR' => 'GB',
                'SVK' => 'SK',
                'IDN' => 'ID',
            ];

            $country_code = $country_translations[$legacy_data->{'Tax Category Country Code'}];


            (new Tax)->updateOrCreate(
                [
                    'legacy_id' => $legacy_data->{'Tax Category Key'},
                ], [
                    'status'       => $legacy_data->{'Tax Category Active'} == 'Yes',
                    'data'         => $tax_data,
                    'code'         => strtolower($legacy_data->{'Tax Category Code'}),
                    'country_code' => $country_code

                ]
            );


        }


        return 0;


    }


}
