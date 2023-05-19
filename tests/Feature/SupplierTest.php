<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 08 May 2023 09:03:42 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Goods\TradeUnit\StoreTradeUnit;
use App\Actions\Procurement\Agent\StoreAgent;
use App\Actions\Procurement\PurchaseOrder\AddItemPurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\StorePurchaseOrder;
use App\Actions\Procurement\Supplier\GetSupplier;
use App\Actions\Procurement\Supplier\StoreSupplier;
use App\Actions\Procurement\SupplierProduct\StoreSupplierProduct;
use App\Actions\Tenancy\Group\StoreGroup;
use App\Actions\Tenancy\Tenant\StoreTenant;
use App\Models\Goods\TradeUnit;
use App\Models\Helpers\Address;
use App\Models\Procurement\Agent;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderItem;
use App\Models\Procurement\Supplier;
use App\Models\Procurement\SupplierProduct;
use App\Models\Tenancy\Group;
use App\Models\Tenancy\Tenant;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

beforeAll(function () {
    loadDB('test_base_database.dump');
});


beforeEach(function () {
    $tenant = Tenant::first();
    if (!$tenant) {
        $group  = StoreGroup::make()->asAction(
            array_merge(
                Group::factory()->definition(),
                [
                    'code'=> 'ACME'
                ]
            )
        );
        $tenant = StoreTenant::make()->action(
            $group,
            array_merge(
                Tenant::factory()->definition(),
                [
                    'code'=> 'AGB'
                ]
            )
        );
        StoreTenant::make()->action(
            $group,
            array_merge(
                Tenant::factory()->definition(),
                [
                    'code'=> 'AUS'
                ]
            )
        );
    }
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
});

test('create independent supplier 2', function () {
    $supplier = StoreSupplier::make()->action(app('currentTenant'), Arr::prepend(Supplier::factory()->definition(), 'supplier', 'type'));
    $this->assertModelExists($supplier);

    return $supplier;
});

test('number independent supplier should be two', function () {
    $this->assertEquals(2, app('currentTenant')->procurementStats->number_suppliers);
});

test('create supplier in agent', function ($agent) {
    $supplier = StoreSupplier::make()->action($agent, Arr::prepend(Supplier::factory()->definition(), 'sub-supplier', 'type'));
    $this->assertModelExists($supplier);
})->depends('create agent');

test('create supplier product', function ($supplier) {
    $supplierProduct = StoreSupplierProduct::make()->action($supplier, SupplierProduct::factory()->definition());
    $this->assertModelExists($supplierProduct);

    return $supplierProduct;
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

test('create trade unit', function () {
    $tradeUnit = StoreTradeUnit::make()->action(TradeUnit::factory()->definition());
    $this->assertModelExists($tradeUnit);

    return $tradeUnit;
});

test('create purchase order', function ($supplier) {
    $purchaseOrder = StorePurchaseOrder::make()->action($supplier, PurchaseOrder::factory()->definition());
    $this->assertModelExists($purchaseOrder);

    return $purchaseOrder;
})->depends('create independent supplier');

test('add items to purchase order', function ($purchaseOrder) {
    $i = 1;
    do {
        $items = AddItemPurchaseOrder::make()->action($purchaseOrder, PurchaseOrderItem::factory()->definition());
        $i++;
    } while ($i <= 5);

    $this->assertModelExists($items);

    return $items->first();
})->depends('create purchase order');
