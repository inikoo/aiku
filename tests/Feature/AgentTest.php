<?php

namespace Tests\Feature;

use App\Actions\Procurement\Agent\StoreAgent;
use App\Models\Assets\Country;
use App\Models\Assets\Currency;
use App\Models\Central\Tenant;
use App\Models\Central\TenantProcurementStats;
use Faker\Factory;
use Tests\TestCase;

class AgentTest extends TestCase
{
    public function test_create_agents()
    {
        $faker = Factory::create();
        $tenant = Tenant::where('code', 'awa')->first();
        $tenant->makeCurrent();

        $currency = Currency::where('code', 'USD')->firstOrFail();
        $country  = Country::where('code', 'US')->firstOrFail();

        $addressData = [];
        $addressData['address_line_1']      = $faker->address;
        $addressData['address_line_2']      = $faker->address;
        $addressData['sorting_code']        = '12-34-56';
        $addressData['postal_code']         = $faker->postcode;
        $addressData['locality']            = $faker->locale;
        $addressData['dependant_locality']  = 'Hometown';
        $addressData['administrative_area'] = 'Apartment';
        $addressData['country_id']          = $country->id;

        $agentData = [
            'code' => 'agent',
            'name' => $faker->name,
            'company_name' => $faker->company,
            'contact_name' => $faker->name,
            'email' => $faker->email,
            'currency_id' => $currency->id,
        ];
        $agent = StoreAgent::make()->action($tenant, $agentData, $addressData);
        $this->assertModelExists($agent);
    }

    public function test_number_of_agents_should_be_one()
    {

        $tenant = Tenant::where('code', 'awa')->first();
        $tenant->makeCurrent();

        $this->assertEquals(1, $tenant->procurementStats->number_agents);
        $this->assertEquals(1, $tenant->procurementStats->number_active_agents);
    }

    public function test_create_another_agents()
    {
        $faker = Factory::create();
        $tenant = Tenant::where('code', 'awa')->first();
        $tenant->makeCurrent();

        $currency = Currency::where('code', 'USD')->firstOrFail();
        $country  = Country::where('code', 'US')->firstOrFail();

        $addressData = [];
        $addressData['address_line_1']      = $faker->address;
        $addressData['address_line_2']      = $faker->address;
        $addressData['sorting_code']        = '12-34-56';
        $addressData['postal_code']         = $faker->postcode;
        $addressData['locality']            = $faker->locale;
        $addressData['dependant_locality']  = 'Hometown';
        $addressData['administrative_area'] = 'Apartment';
        $addressData['country_id']          = $country->id;

        $agentData = [
            'code' => 'agents',
            'name' => $faker->name,
            'company_name' => $faker->company,
            'contact_name' => $faker->name,
            'email' => $faker->email,
            'currency_id' => $currency->id,
        ];

        $agent = StoreAgent::make()->action($tenant, $agentData, $addressData);
        $this->assertModelExists($agent);
    }

    public function test_number_of_agents_should_be_two()
    {

        $tenant = Tenant::where('code', 'awa')->first();
        $tenant->makeCurrent();

        $this->assertEquals(2, $tenant->procurementStats->number_agents);
        $this->assertEquals(2, $tenant->procurementStats->number_active_agents);
    }
}
