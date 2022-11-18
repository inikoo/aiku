<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 21 Sept 2022 14:51:12 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

/** @noinspection PhpUnused */

namespace App\Actions\Central\Tenant;


use App\Models\Assets\Country;
use App\Models\Assets\Currency;
use App\Models\Assets\Language;
use App\Models\Assets\Timezone;
use App\Models\Central\Tenant;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateAuroraTenant
{
    use AsAction;


    public string $commandSignature = 'create:tenant-aurora {code} {aurora_db} {email}';
    public string $commandDescription = 'Crete new Aurora tenant';


    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function asCommand(Command $command): int
    {
        if ($tenant = Tenant::where('code', $command->argument('code'))->first()) {
            $command->error("Tenant $tenant->code already exists");

            return 1;
        }

        $aurora_db = $command->argument('aurora_db');
        $code      = $command->argument('code');
        $email     = $command->argument('email');

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

            return 1;
        }


        $language = substr($auData->{'Account Locale'}, 0, 2);


        $country  = Country::where('code', $auData->{'Account Country 2 Alpha Code'})->firstOrFail();
        $language = Language::where('code', $language)->firstOrFail();
        $timezone = Timezone::where('name', $auData->{'Account Timezone'})->firstOrFail();
        $currency = Currency::where('code', $auData->{'Account Currency'})->firstOrFail();

        $tenantData = [
            'code'        => $code,
            'name'        => $auData->{'Account Name'},
            'country_id'  => $country->id,
            'language_id' => $language->id,
            'timezone_id' => $timezone->id,
            'currency_id' => $currency->id,
            'source'      => [
                'type'         => 'Aurora',
                'db_name'      => $aurora_db,
                'account_code' => $auData->{'Account Code'},
            ]
        ];


        $tenant = StoreTenant::run($tenantData);


        $domain = $tenant->domains()->create([
                                                 'domain' => $tenant->code,
                                             ]);


        $result = CreateTenantAdminUser::run(
            $tenant,
            [
                [
                    'username' => $tenant->code,
                    'email'    => $email,
                    'password' => (app()->isLocal() ? 'hello' : wordwrap(Str::random(), 4, '-', true))
                ]
            ],
            [
                'name'      => 'aurora',
                'abilities' => ['aurora']
            ]
        );


        $token = $result->token;


        DB::connection('aurora')->table('Account Data')
            ->update(['pika_token' => $token]);


        Artisan::call('tenants:seed');
        Artisan::call('create:tenant-storage-link');


        DB::connection('aurora')->table('Account Data')
            ->update(['pika_url' => (app()->isLocal() ? 'http://' : 'https://').$domain->domain.'.'.config('app.domain')]);


        $command->table(
            ['Tenant', 'Token'],
            [
                [
                    $tenant->code,
                    $token
                ],

            ]
        );

        return 0;
    }

}
