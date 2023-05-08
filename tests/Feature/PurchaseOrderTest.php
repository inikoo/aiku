<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 08 May 2023 09:03:42 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Procurement\Agent\StoreAgent;
use App\Actions\Procurement\PurchaseOrder\AddItemPurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\DeletePurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\StorePurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\SubmitPurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UnSubmitPurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdatePurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdatePurchaseOrderItemQuantity;
use App\Actions\Procurement\Supplier\StoreSupplier;
use App\Actions\Procurement\SupplierProduct\StoreSupplierProduct;
use App\Models\Helpers\Address;
use App\Models\Procurement\Agent;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderItem;
use App\Models\Procurement\Supplier;
use App\Models\Procurement\SupplierProduct;
use App\Models\Tenancy\Tenant;
use Illuminate\Validation\ValidationException;

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

test('create purchase order', function ($supplier) {
    $purchaseOrder = StorePurchaseOrder::make()->action($supplier, PurchaseOrder::factory()->definition());
    $this->assertModelExists($purchaseOrder);

    return $purchaseOrder;
})->depends('create independent supplier');

test('check has one purchase order', function ($supplier) {
    $this->assertEquals(1, $supplier->stats->number_purchase_orders);
})->depends('create independent supplier');

test('create new purchase order', function ($supplier) {
    expect(function () use ($supplier) {
        StorePurchaseOrder::make()->action($supplier, PurchaseOrder::factory()->definition());
    })->toThrow(ValidationException::class);
})->depends('create independent supplier');

test('create new purchase order by force', function ($supplier) {
    $purchaseOrder = StorePurchaseOrder::make()->action($supplier, PurchaseOrder::factory()->definition(), true);
    $this->assertModelExists($purchaseOrder);

    return $purchaseOrder;
})->depends('create independent supplier');

test('order', function ($supplier) {
    $this->assertEquals(1, $supplier->stats->number_purchase_orders);
})->depends('create independent supplier');

test('create supplier product', function ($supplier) {
    $supplierProduct = StoreSupplierProduct::make()->action($supplier, SupplierProduct::factory()->definition());
    $this->assertModelExists($supplierProduct);
})->depends('create independent supplier');

test('delete purchase order when items 0', function ($purchaseOrder) {
    expect(function () use ($purchaseOrder) {
        DeletePurchaseOrder::make()->action($purchaseOrder->first());
    })->toThrow(ValidationException::class);
})->depends('create new purchase order by force');

test('add items to purchase order', function ($purchaseOrder) {
    $i = 1;
    do {
        $items = AddItemPurchaseOrder::make()->action($purchaseOrder, PurchaseOrderItem::factory()->definition());
        $i++;
    } while ($i <= 5);

    $this->assertModelExists($items);

    return $items->first();
})->depends('create new purchase order by force');

test('delete purchase order', function ($purchaseOrder) {
    $purchaseOrder = DeletePurchaseOrder::make()->action($purchaseOrder->fresh());

    expect($purchaseOrder)->toBeTrue();
})->depends('create new purchase order by force');

test('update quantity items to 0 in purchase order', function ($item) {
    $item = UpdatePurchaseOrderItemQuantity::make()->action($item, [
        'unit_quantity' => 0
    ]);

    $this->assertModelMissing($item);
})->depends('add items to purchase order');

test('update quantity items in purchase order', function ($item) {
    $item = UpdatePurchaseOrderItemQuantity::make()->action($item, [
        'unit_quantity' => 12
    ]);

    $this->assertModelMissing($item);
})->depends('add items to purchase order');

test('update purchase order', function ($agent) {
    $purchaseOrder = UpdatePurchaseOrder::make()->action($agent, PurchaseOrder::factory()->definition());
    $this->assertModelExists($purchaseOrder);
})->depends('create new purchase order by force');

test('create purchase order by agent', function ($agent) {
    $purchaseOrder = StorePurchaseOrder::make()->action($agent, PurchaseOrder::factory()->definition());
    $this->assertModelExists($purchaseOrder);
})->depends('create agent');

test('submit purchase order', function ($purchaseOrder) {
    $purchaseOrder = SubmitPurchaseOrder::make()->action($purchaseOrder);
    $this->assertModelExists($purchaseOrder);
})->depends('create new purchase order by force');

test('un submit purchase order', function ($purchaseOrder) {
    $purchaseOrder = UnSubmitPurchaseOrder::make()->action($purchaseOrder);
    $this->assertModelExists($purchaseOrder);
})->depends('create new purchase order by force');
