<?php

namespace Tests\Feature;

use App\Actions\Central\Group\StoreGroup;
use App\Actions\Central\Tenant\StoreTenant;
use App\Models\Assets\Country;
use App\Models\Assets\Currency;
use App\Models\Assets\Language;
use App\Models\Assets\Timezone;
use App\Models\Central\Group;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TenantTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_create_tenants()
    {
        $faker = Factory::create();
        $group = Group::factory()->create();

        $country = Country::where('code', 'US')->firstOrFail();
        $language = Language::where('code', $faker->languageCode)->firstOrFail();
        $timezone = Timezone::where('name', $faker->timezone('USA'))->firstOrFail();
        $currency = Currency::where('code', 'USD')->firstOrFail();

        $tenantData = [
            'code' => 'awa',
            'name' => $faker->name,
            'country_id' => $country->id,
            'language_id' => $language->id,
            'timezone_id' => $timezone->id,
            'currency_id' => $currency->id,
        ];

        $tenant = StoreTenant::make()->action($group, $tenantData);

        $this->assertModelExists($tenant);
    }

    public function create_three_tenants()
    {
        //
    }
}
