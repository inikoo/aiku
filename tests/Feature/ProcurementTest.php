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

test('create agent', function () {
    $agent = StoreAgent::make()->action($this->tenant, Agent::factory()->definition(), Address::factory()->definition());
    $this->assertModelExists($agent);
    return $agent;
});

test('number of agents should be one', function () {
    $this->assertEquals(1, $this->tenant->procurementStats->number_agents);
    $this->assertEquals(1, $this->tenant->procurementStats->number_active_agents);
})->depends('create agent');

test('create another agent', function () {
    $agent = StoreAgent::make()->action($this->tenant, Agent::factory()->definition(), Address::factory()->definition());
    $this->assertModelExists($agent);
});

test('number of agents should be two', function () {
    $this->assertEquals(2, $this->tenant->procurementStats->number_agents);
    $this->assertEquals(2, $this->tenant->procurementStats->number_active_agents);
})->depends('create agent', 'create another agent');

test('create supplier', function () {
    $supplier = StoreSupplier::make()->action($this->tenant, Supplier::factory()->definition());
    $this->assertModelExists($supplier);
    return $supplier;
});

test('create supplier in agent', function ($agent) {
    $supplier = StoreSupplier::make()->action($agent, Supplier::factory()->definition());
    $this->assertModelExists($supplier);
})->depends('create agent');


test('number of supplier should be two', function () {
    $this->assertEquals(2, $this->tenant->procurementStats->number_suppliers);
    $this->assertEquals(2, $this->tenant->procurementStats->number_active_suppliers);
})->depends('create supplier', 'create supplier in agent');

test('create supplier product', function ($supplier) {
    $supplierProduct = StoreSupplierProduct::make()->action($supplier, SupplierProduct::factory()->definition());
    $this->assertModelExists($supplierProduct);
})->depends('create supplier');

test('create purchase order', function ($supplier) {
    $purchaseOrder = StorePurchaseOrder::make()->action($supplier, PurchaseOrder::factory()->definition());
    $this->assertModelExists($purchaseOrder);
})->depends('create supplier');
