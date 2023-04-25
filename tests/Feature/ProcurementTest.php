<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Apr 2023 15:51:21 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Procurement\Agent\StoreAgent;
use App\Actions\Procurement\Supplier\StoreSupplier;
use App\Models\Helpers\Address;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use App\Models\Tenancy\Tenant;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

test('create agents', function () {
    $tenant = Tenant::where('slug', 'agb')->first();

    $tenant->makeCurrent();

    $agent = StoreAgent::make()->action($tenant, Agent::factory()->definition(), Address::factory()->definition());
    $this->assertModelExists($agent);
});

test('number of agents should be one', function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();

    $this->assertEquals(1, $tenant->procurementStats->number_agents);
    $this->assertEquals(1, $tenant->procurementStats->number_active_agents);
});

test('create another agents', function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();

    $agent = StoreAgent::make()->action($tenant, Agent::factory()->definition(), Address::factory()->definition());

    $this->assertModelExists($agent);
});

test('number of agents should be two', function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();

    $this->assertEquals(2, $tenant->procurementStats->number_agents);
    $this->assertEquals(2, $tenant->procurementStats->number_active_agents);
});

test('create supplier', function () {
     $tenant = Tenant::where('slug', 'agb')->first();
     $tenant->makeCurrent();

     $supplier = StoreSupplier::make()->action($tenant, Supplier::factory()->definition());

     $this->assertModelExists($supplier);
});

test('create agent with the supplier', function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();

    $agent = StoreAgent::make()->action($tenant, Agent::factory()->definition(), Address::factory()->definition());

    $supplier = StoreSupplier::make()->action($agent, Supplier::factory()->definition());

    $this->assertModelExists($supplier);
});

test('number of agents should be three', function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();

    $this->assertEquals(3, $tenant->procurementStats->number_agents);
    $this->assertEquals(3, $tenant->procurementStats->number_active_agents);
});

test('number of supplier should be two', function () {

    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();

    $this->assertEquals(2, $tenant->procurementStats->number_suppliers);
    $this->assertEquals(2, $tenant->procurementStats->number_active_suppliers);
});
