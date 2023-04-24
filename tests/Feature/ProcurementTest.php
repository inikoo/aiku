<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Apr 2023 15:51:21 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Procurement\Agent\StoreAgent;
use App\Models\Assets\Currency;
use App\Models\Helpers\Address;
use App\Models\Procurement\Agent;
use App\Models\Tenancy\Tenant;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

test('create agents', function () {
    $tenant = Tenant::where('slug', 'agb')->first();


    $tenant->makeCurrent();

    $agent = StoreAgent::make()->action($tenant, Agent::factory()->definition(), Address::factory()->definition());
    $this->assertModelExists($agent);
});

test('number of agents should be one', function () {
    $tenant = Tenant::where('slug', 'awa')->first();
    $tenant->makeCurrent();

    $this->assertEquals(1, $tenant->procurementStats->number_agents);
    $this->assertEquals(1, $tenant->procurementStats->number_active_agents);
});


test('create another agents', function () {
    $tenant = Tenant::where('slug', 'awa')->first();
    $tenant->makeCurrent();

    $currency = Currency::where('code', 'USD')->firstOrFail();

    $agentData = [
        'code'         => 'agents',
        'name'         => fake()->name,
        'company_name' => fake()->company,
        'contact_name' => fake()->name,
        'email'        => fake()->email,
        'currency_id'  => $currency->id,
    ];

    $agent = StoreAgent::make()->action($tenant, $agentData, Address::factory()->definition());

    $this->assertModelExists($agent);
});

/*
 public function test_number_of_agents_should_be_two()
 {

     $tenant = Tenant::where('slug', 'awa')->first();
     $tenant->makeCurrent();

     $this->assertEquals(2, $tenant->procurementStats->number_agents);
     $this->assertEquals(2, $tenant->procurementStats->number_active_agents);
 }

 public function test_create_supplier()
 {
     $faker = Factory::create();

     $tenant = Tenant::where('slug', 'awa')->first();
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
     $tenant = Tenant::where('slug', 'awa')->first();
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
     $tenant = Tenant::where('slug', 'awa')->first();
     $tenant->makeCurrent();

     $this->assertEquals(3, $tenant->procurementStats->number_agents);
     $this->assertEquals(3, $tenant->procurementStats->number_active_agents);
 }

 public function test_number_of_supplier_should_be_two()
 {
     $tenant = Tenant::where('slug', 'awa')->first();
     $tenant->makeCurrent();

     $this->assertEquals(2, $tenant->procurementStats->number_suppliers);
     $this->assertEquals(2, $tenant->procurementStats->number_active_suppliers);
 }
 */
