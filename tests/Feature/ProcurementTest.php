<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Apr 2023 15:51:21 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Procurement\Agent\StoreAgent;
use App\Actions\Procurement\PurchaseOrder\StorePurchaseOrder;
use App\Actions\Procurement\Supplier\StoreSupplier;
use App\Actions\Procurement\SupplierProduct\StoreSupplierProduct;
use App\Models\Helpers\Address;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use App\Models\Procurement\SupplierProduct;
use App\Models\Tenancy\Tenant;
use App\Models\Procurement\PurchaseOrder;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $this->tenant = Tenant::where('slug', 'agb')->first();
    $this->tenant->makeCurrent();
});

test('create agents', function () {
    $agent = StoreAgent::make()->action($this->tenant, Agent::factory()->definition(), Address::factory()->definition());
    $this->assertModelExists($agent);
});

test('number of agents should be one', function () {


    $this->assertEquals(1, $this->tenant->procurementStats->number_agents);
    $this->assertEquals(1, $this->tenant->procurementStats->number_active_agents);
});

test('create another agents', function () {


    $agent = StoreAgent::make()->action($this->tenant, Agent::factory()->definition(), Address::factory()->definition());

    $this->assertModelExists($agent);
});

test('number of agents should be two', function () {

    $this->assertEquals(2, $this->tenant->procurementStats->number_agents);
    $this->assertEquals(2, $this->tenant->procurementStats->number_active_agents);
});

test('create supplier', function () {


     $supplier = StoreSupplier::make()->action($this->tenant, Supplier::factory()->definition());

     $this->assertModelExists($supplier);
});

test('create agent with the supplier', function () {


    $agent = StoreAgent::make()->action($this->tenant, Agent::factory()->definition(), Address::factory()->definition());

    $supplier = StoreSupplier::make()->action($agent, Supplier::factory()->definition());

    $this->assertModelExists($supplier);
});

test('number of agents should be three', function () {


    $this->assertEquals(3, $this->tenant->procurementStats->number_agents);
    $this->assertEquals(3, $this->tenant->procurementStats->number_active_agents);
});

test('number of supplier should be two', function () {


    $this->assertEquals(2, $this->tenant->procurementStats->number_suppliers);
    $this->assertEquals(2, $this->tenant->procurementStats->number_active_suppliers);
});

test('create supplier product', function () {

    $supplier = Supplier::latest()->first();

    $purchaseOrder = StoreSupplierProduct::make()->action($supplier, SupplierProduct::factory()->definition());

    $this->assertModelExists($purchaseOrder);
});

test('create purchase order', function () {

    $supplier = Supplier::latest()->first();

    $purchaseOrder = StorePurchaseOrder::make()->action($supplier, PurchaseOrder::factory()->definition());

    $this->assertModelExists($purchaseOrder);
});
