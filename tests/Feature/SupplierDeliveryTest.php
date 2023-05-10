<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 10 May 2023 14:06:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Goods\TradeUnit\StoreTradeUnit;
use App\Actions\Procurement\Agent\StoreAgent;
use App\Actions\Procurement\PurchaseOrder\AddItemPurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\StorePurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdateStateToSubmittedPurchaseOrder;
use App\Actions\Procurement\Supplier\GetSupplier;
use App\Actions\Procurement\Supplier\StoreSupplier;
use App\Actions\Procurement\SupplierDelivery\StoreSupplierDelivery;
use App\Actions\Procurement\SupplierDelivery\UpdateStateToCreatingSupplierDelivery;
use App\Actions\Procurement\SupplierDeliveryItem\StoreSupplierDeliveryItem;
use App\Actions\Procurement\SupplierDeliveryItem\StoreSupplierDeliveryItemBySelectedPurchaseOrderItem;
use App\Actions\Procurement\SupplierProduct\StoreSupplierProduct;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\SupplierDelivery\SupplierDeliveryStateEnum;
use App\Models\Goods\TradeUnit;
use App\Models\Helpers\Address;
use App\Models\Procurement\Agent;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderItem;
use App\Models\Procurement\Supplier;
use App\Models\Procurement\SupplierDelivery;
use App\Models\Procurement\SupplierProduct;
use App\Models\Tenancy\Tenant;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();
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

test('create supplier product', function ($supplier) {
    $supplierProduct = StoreSupplierProduct::make()->action($supplier, SupplierProduct::factory()->definition());
    $this->assertModelExists($supplierProduct);

    return $supplierProduct;
})->depends('create independent supplier');

test('create supplier delivery', function ($supplier) {
    $supplierDelivery = StoreSupplierDelivery::make()->action($supplier, SupplierDelivery::factory()->definition());
    $this->assertModelExists($supplierDelivery);

    return $supplierDelivery;
})->depends('create independent supplier');

test('create supplier delivery items', function ($supplierDelivery) {
    $supplier = StoreSupplierDeliveryItem::run($supplierDelivery, PurchaseOrderItem::factory()->definition());
    $this->assertModelExists($supplier);
})->depends('create supplier delivery');

test('add items to purchase order', function ($purchaseOrder) {
    $i = 1;
    do {
        $items = AddItemPurchaseOrder::make()->action($purchaseOrder, PurchaseOrderItem::factory()->definition());
        $i++;
    } while ($i <= 5);

    $this->assertModelExists($items);

    return $items;
})->depends('create purchase order');

test('create supplier delivery items by selected purchase order', function ($supplierDelivery, $items) {
    $supplier = StoreSupplierDeliveryItemBySelectedPurchaseOrderItem::
    run($supplierDelivery, $items->pluck('id')->toArray());
    expect($supplier)->toBeTrue();
})->depends('create supplier delivery', 'add items to purchase order');

test('change state to creating supplier delivery', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToCreatingSupplierDelivery::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(SupplierDeliveryStateEnum::CREATING);
})->depends('create supplier delivery');

test('change state to dispatch from creating supplier delivery', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToCreatingSupplierDelivery::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(SupplierDeliveryStateEnum::CREATING);
})->depends('create supplier delivery');
