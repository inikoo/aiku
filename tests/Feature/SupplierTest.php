<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 08 May 2023 09:03:42 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Procurement\Agent\StoreAgent;
use App\Actions\Procurement\Supplier\GetSupplier;
use App\Actions\Procurement\Supplier\StoreSupplier;
use App\Actions\Procurement\SupplierProduct\StoreSupplierProduct;
use App\Models\Helpers\Address;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use App\Models\Procurement\SupplierProduct;
use App\Models\Tenancy\Tenant;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

beforeAll(fn() => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();
});

test('create agent', function () {
    $agent = StoreAgent::make()->action(app('currentTenant'), Agent::factory()->definition(), Address::factory()->definition());
    $this->assertModelExists($agent);
    return $agent;
});

test('create independent supplier', function () {
    $supplier = StoreSupplier::make()->action(app('currentTenant'), Arr::prepend(Supplier::factory()->definition(), 'supplier', 'type'));
    $this->assertModelExists($supplier);

    return $supplier;
});

test('number independent supplier should be one', function () {
    $this->assertEquals(1, app('currentTenant')->procurementStats->number_suppliers);
    $this->assertEquals(1, app('currentTenant')->procurementStats->number_active_suppliers);
});

test('create independent supplier 2', function () {
    $supplier = StoreSupplier::make()->action(app('currentTenant'), Arr::prepend(Supplier::factory()->definition(), 'supplier', 'type'));
    $this->assertModelExists($supplier);

    return $supplier;
});

test('number independent supplier should be two', function () {
    $this->assertEquals(2, app('currentTenant')->procurementStats->number_suppliers);
    $this->assertEquals(2, app('currentTenant')->procurementStats->number_active_suppliers);
});

test('create supplier in agent', function ($agent) {
    $supplier = StoreSupplier::make()->action($agent, Arr::prepend(Supplier::factory()->definition(), 'sub-supplier', 'type'));
    $this->assertModelExists($supplier);
})->depends('create agent');

test('create supplier product', function ($supplier) {
    $supplierProduct = StoreSupplierProduct::make()->action($supplier, SupplierProduct::factory()->definition());
    $this->assertModelExists($supplierProduct);
})->depends('create independent supplier');

test('create supplier product 2', function ($supplier) {
    $supplierProduct = StoreSupplierProduct::make()->action($supplier, SupplierProduct::factory()->definition());
    $this->assertModelExists($supplierProduct);
})->depends('create independent supplier');

test('others tenant can view supplier', function ($agent) {
    $tenant = Tenant::where('slug', 'aus')->first();
    $tenant->makeCurrent();

    $supplier = GetSupplier::run($agent);

    expect($supplier)->toBeInstanceOf(LengthAwarePaginator::class);
})->depends('create agent');
