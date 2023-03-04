<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 21 Sept 2022 14:51:12 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


namespace App\Actions\Central\Tenant;


use App\Models\Assets\Country;
use App\Models\Assets\Currency;
use App\Models\Assets\Language;
use App\Models\Assets\Timezone;
use App\Models\Central\Tenant;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateAuroraTenant
{
    use AsAction;


    public string $commandSignature = 'create:tenant-aurora {code} {aurora_db} {email}';
    public function getCommandDescription(): string
    {
        return 'Crete new Aurora tenant.';
    }


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


        if (App::environment('local')) {
            /** @noinspection HttpUrlsUsage */
            $auroraURL = "http://".env('AURORA_DOMAIN', 'aurora.local');
        } else {
            $auroraURL = "https://$code.".env('AURORA_DOMAIN', 'aurora.systems');
        }


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
                'url'          => $auroraURL
            ]
        ];


        $tenant = StoreTenant::run($tenantData);


        $accountsServiceProviderData = Db::connection('aurora')->table('Payment Service Provider Dimension')
            ->select('Payment Service Provider Key')
            ->where('Payment Service Provider Block', 'Accounts')->first();

        if ($accountsServiceProviderData) {
            $tenant->execute(fn(Tenant $tenant) => $tenant->accountsServiceProvider()->update(
                [
                    'source_id' => $accountsServiceProviderData->{'Payment Service Provider Key'}
                ]
            ));
        }


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


        //Artisan::call('tenants:seed');
        //Artisan::call('create:tenant-storage-link');


        /** @noinspection HttpUrlsUsage */
        DB::connection('aurora')->table('Account Data')
            ->update(['pika_url' => (app()->isLocal() ? 'http://' : 'https://').$tenant->slug.'.'.config('app.domain')]);


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
