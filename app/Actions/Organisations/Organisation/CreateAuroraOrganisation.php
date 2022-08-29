<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 12 Aug 2022 17:41:58 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Organisations\Organisation;

use App\Models\Assets\Country;
use App\Models\Assets\Currency;
use App\Models\Assets\Language;
use App\Models\Assets\Timezone;
use App\Models\Organisations\Organisation;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateAuroraOrganisation
{
    use AsAction;


    public string $commandSignature = 'org:aurora {code} {aurora_db}';
    public string $commandDescription = 'Crete new Aurora organisation';

    public function handle($code,$aurora_db)
    {
        $data = [];

        $database_settings = data_get(config('database.connections'), 'aurora');
        data_set($database_settings, 'database', $aurora_db);
        config(['database.connections.aurora' => $database_settings]);
        DB::connection('aurora');
        DB::purge('aurora');


        try {
            $auData = DB::connection('aurora')->table('Account Dimension')
                          ->where('Account Key', 1)->get()[0];
        } catch (Exception $e) {
            echo $e->getMessage();

            return 0;
        }


        $data['account_code'] = $auData->{'Account Code'};
        $data['db_name']      = $aurora_db;

        $language = substr($auData->{'Account Locale'}, 0, 2);


        $country  = Country::where('code', $auData->{'Account Country 2 Alpha Code'})->firstOrFail();
        $language = Language::where('code', $language)->firstOrFail();
        $timezone = Timezone::where('name', $auData->{'Account Timezone'})->firstOrFail();
        $currency = Currency::where('code', $auData->{'Account Currency'})->firstOrFail();

        $organisationData = [
            'code'        => $code,
            'name'        => $auData->{'Account Name'},
            'type'        => 'Aurora',
            'country_id'  => $country->id,
            'language_id' => $language->id,
            'timezone_id' => $timezone->id,
            'currency_id' => $currency->id,
            'data'        => $data,

        ];


        $res = StoreOrganisation::run($organisationData);


        $token = $res->model->createToken('au-bridge', ['bridge'])->plainTextToken;
        DB::connection('aurora')->table('Account Data')
            ->update(['pika_token' => $token]);

        $res->data['token']=$token;

        return $res;
    }

    public function asCommand(Command $command): void
    {
        if ($organisation = Organisation::where('code', $command->argument('aurora_db'))->first()) {
            $command->error("Organisation $organisation->code already exists");

            return;
        }


        $res = $this->handle($command->argument('code'),$command->argument('aurora_db'));


        $command->table(
            ['Organisation', 'Token'],
            [
                [
                    $res->model->code,
                    $res->data['token']
                ],

            ]
        );
    }

}
