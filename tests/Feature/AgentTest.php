<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 22 Apr 2023 09:20:50 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use Tests\TestCase;

class AgentTest extends TestCase
{
    /*
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $process = new Process(['/home/raul/aiku/devops/devel/reset_test_database.sh', 'aiku_test','raul','hello']);
        $process->run();

    }

    public function test_create_agents()
    {

        $faker = Factory::create();


        $tenant = Tenant::where('code', 'awa')->first();

        dd($tenant);
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

    public function test_create_supplier()
    {
        $faker = Factory::create();

        $tenant = Tenant::where('code', 'awa')->first();
        $tenant->makeCurrent();

        $currency = Currency::where('code', 'USD')->firstOrFail();
        $group = Group::where('code', 'aw')->firstOrFail();

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
    */

}
