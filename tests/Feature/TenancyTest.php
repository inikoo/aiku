<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 21 Apr 2023 16:21:46 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Tenancy\Group\StoreGroup;
use App\Actions\Tenancy\Tenant\StoreTenant;
use App\Models\Assets\Country;
use App\Models\Assets\Currency;
use App\Models\Assets\Language;
use App\Models\Assets\Timezone;
use App\Models\Tenancy\Group;
use Faker\Factory;
use Symfony\Component\Process\Process;

beforeAll(function () {

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../../','.env.testing');
    $dotenv->load();

    $process = new Process([
            __DIR__.'/../../devops/devel/reset_test_database.sh',
            env('DB_DATABASE_TEST','aiku_test'),
            env('DB_USERNAME'),
            env('DB_PASSWORD')
        ]
    );
    $process->run();
});


test('create group using action', function () {
    $groupData = [
        'code'        => 'hello',
        'name'        => 'Hello Ltd',
        'currency_id' => 1
    ];
    $group     = StoreGroup::make()->asAction($groupData);
    $this->assertModelExists($group);
});


test('create group using command', function () {
    $this->artisan('create:group acme "Acme Inc" USD')->assertSuccessful();
});


test('add tenant to group', function () {
    $faker = Factory::create();

    $group = Group::where('slug', 'hello')->firstOrFail();

    $country  = Country::where('code', 'US')->firstOrFail();
    $language = Language::where('code', $faker->languageCode)->firstOrFail();
    $timezone = Timezone::where('name', $faker->timezone('USA'))->firstOrFail();
    $currency = Currency::where('code', 'USD')->firstOrFail();

    $tenantData = [
        'code'        => 'awa',
        'name'        => $faker->name,
        'country_id'  => $country->id,
        'language_id' => $language->id,
        'timezone_id' => $timezone->id,
        'currency_id' => $currency->id,
    ];


    $tenant = StoreTenant::make()->action($group, $tenantData);

    $this->assertModelExists($tenant);
});


test('try to create group with wrong currency', function () {
    $this->artisan('create:group fail "Fail Inc" XXX')->assertFailed();
});

test('try to create group with duplicated code', function () {
    $this->artisan('create:group fail "Fail Inc" XXX')->assertFailed();
});
