<?php

namespace Tests\Feature;

use App\Actions\Procurement\Agent\StoreAgent;
use App\Actions\Procurement\Supplier\StoreSupplier;
use App\Models\Assets\Country;
use App\Models\Assets\Currency;
use App\Models\Tenancy\Tenant;
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

        $agentData = [
            'code' => 'agent',
            'name' => $faker->name,
            'company_name' => $faker->company,
            'contact_name' => $faker->name,
            'email' => $faker->email,
            'currency_id' => $currency->id,
        ];
        $agent = StoreAgent::make()->action($tenant, $agentData, $this->getAddress($faker));
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

        $agentData = [
            'code' => 'agents',
            'name' => $faker->name,
            'company_name' => $faker->company,
            'contact_name' => $faker->name,
            'email' => $faker->email,
            'currency_id' => $currency->id,
        ];

        $agent = StoreAgent::make()->action($tenant, $agentData, $this->getAddress($faker));
        $this->assertModelExists($agent);
    }

    public function test_number_of_agents_should_be_two()
    {

        $tenant = Tenant::where('code', 'awa')->first();
        $tenant->makeCurrent();

        $this->assertEquals(2, $tenant->procurementStats->number_agents);
        $this->assertEquals(2, $tenant->procurementStats->number_active_agents);
    }

    public function test_create_supplier()
    {
        $faker = Factory::create();

        $tenant = Tenant::where('code', 'awa')->first();
        $tenant->makeCurrent();

        $currency = Currency::where('code', 'USD')->firstOrFail();

        $supplierData = [
            'code' => 'supplier',
            'name' => $faker->name,
            'company_name' => $faker->company,
            'contact_name' => $faker->name,
            'email' => $faker->email,
            'currency_id' => $currency->id,
            'type' => 'supplier',
            'address_id' => 1
        ];

        $supplier = StoreSupplier::make()->action($tenant, $supplierData);

        $this->assertModelExists($supplier);
    }

    public function test_create_agent_with_the_supplier()
    {
        $faker = Factory::create();
        $tenant = Tenant::where('code', 'awa')->first();
        $tenant->makeCurrent();

        $currency = Currency::where('code', 'IDR')->firstOrFail();

        $agentData = [
            'code' => 'agentp',
            'name' => $faker->name,
            'company_name' => $faker->company,
            'contact_name' => $faker->name,
            'email' => $faker->email,
            'currency_id' => $currency->id,
        ];

        $agent = StoreAgent::make()->action($tenant, $agentData, $this->getAddress($faker));
        $currency = Currency::where('code', 'IDR')->firstOrFail();

        $supplierData = [
            'code' => 'supplier',
            'name' => $faker->name,
            'company_name' => $faker->company,
            'contact_name' => $faker->name,
            'email' => $faker->email,
            'currency_id' => $currency->id,
            'type' => 'supplier',
        ];

        $supplier = StoreSupplier::make()->action($agent, $supplierData);

        $this->assertModelExists($supplier);
    }

    public function test_number_of_agents_should_be_three()
    {
        $tenant = Tenant::where('code', 'awa')->first();
        $tenant->makeCurrent();

        $this->assertEquals(3, $tenant->procurementStats->number_agents);
        $this->assertEquals(3, $tenant->procurementStats->number_active_agents);
    }

    public function test_number_of_supplier_should_be_two()
    {
        $tenant = Tenant::where('code', 'awa')->first();
        $tenant->makeCurrent();

        $this->assertEquals(2, $tenant->procurementStats->number_suppliers);
        $this->assertEquals(2, $tenant->procurementStats->number_active_suppliers);
    }

    public function getAddress($faker): array
    {
        $country = Country::where('code', 'CN')->firstOrFail();
        $addressData = [];
        $addressData['address_line_1'] = $faker->address;
        $addressData['address_line_2'] = $faker->address;
        $addressData['sorting_code'] = '12-34-56';
        $addressData['postal_code'] = $faker->postcode;
        $addressData['locality'] = $faker->locale;
        $addressData['dependant_locality'] = 'Hometown';
        $addressData['administrative_area'] = 'Apartment';
        $addressData['country_id'] = $country->id;

        return $addressData;
    }
}
