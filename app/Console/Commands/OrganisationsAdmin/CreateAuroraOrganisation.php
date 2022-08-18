<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 12 Aug 2022 17:41:58 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Console\Commands\OrganisationsAdmin;

use App\Actions\Organisations\Organisation\StoreOrganisation;
use App\Models\Assets\Country;
use App\Models\Assets\Language;
use App\Models\Assets\Timezone;
use App\Models\Organisations\Organisation;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateAuroraOrganisation extends Command
{

    protected $signature = 'org:aurora {aurora_db}';


    protected $description = 'Crete new Aurora organisation';


    public function handle(): int
    {
        $organisation = Organisation::where('code', $this->argument('aurora_db'))->first();
        if ($organisation) {
            $this->error("Organisation $organisation->code already exists");

            return 0;
        }

        $data = [];

        $database_settings = data_get(config('database.connections'), 'aurora');
        data_set($database_settings, 'database', $this->argument('aurora_db'));
        config(['database.connections.aurora' => $database_settings]);
        DB::connection('aurora');
        DB::purge('aurora');


        try {
            $auData = DB::connection('aurora')->table('Account Dimension')
                          ->where('Account Key', '=', 1)->get()[0];
        } catch (Exception $e) {
            echo $e->getMessage();

            return 0;
        }


        $data['aurora_account_code'] = $auData->{'Account Code'};

        $language=substr($auData->{'Account Locale'}, 0, 2);



        $country  = Country::where('code', $auData->{'Account Country 2 Alpha Code'})->firstOrFail();
        $language = Language::where('code', $language)->firstOrFail();
        $timezone = Timezone::where('name', $auData->{'Account Timezone'})->firstOrFail();

        $organisationData = [
            'code'        => $this->argument('aurora_db'),
            'name'        => $auData->{'Account Name'},
            'type'        => 'Aurora',
            'country_id'  => $country->id,
            'language_id' => $language->id,
            'timezone_id' => $timezone->id,
            'data'        => $data,

        ];


        $res=StoreOrganisation::run($organisationData);

        $organisation = $res->model;

        $token=$organisation->createToken('au-bridge',['bridge'])->plainTextToken;
        DB::connection('aurora')->table('Account Data')
            ->update(['pika_token' => $token]);



        $this->table(
            ['Organisation', 'Token'],
            [
                [
                    $organisation->code,
                    $token
                ],

            ]
        );


        return 0;
    }
}
