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
use App\Actions\Procurement\PurchaseOrder\DeletePurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\StorePurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdatePurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdatePurchaseOrderItemQuantity;
use App\Actions\Procurement\PurchaseOrder\UpdateStateToCheckedPurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdateStateToConfirmPurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdateStateToCreatingPurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdateStateToDispatchedPurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdateStateToManufacturedPurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdateStateToReceivedPurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdateStateToSettledPurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdateStateToSubmittedPurchaseOrder;
use App\Actions\Procurement\Supplier\StoreSupplier;
use App\Actions\Procurement\SupplierProduct\StoreSupplierProduct;
use App\Actions\Procurement\SupplierProduct\SyncSupplierProductTradeUnits;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Models\Goods\TradeUnit;
use App\Models\Helpers\Address;
use App\Models\Procurement\Agent;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderItem;
use App\Models\Procurement\Supplier;
use App\Models\Procurement\SupplierProduct;
use App\Models\Tenancy\Tenant;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

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

test('create supplier with agent', function ($agent) {
    $supplier = StoreSupplier::make()->action($agent, Arr::prepend(Supplier::factory()->definition(), 'supplier', 'type'));
    $this->assertModelExists($supplier);

    return $supplier;
})->depends('create agent');

test('create supplier product', function ($supplier) {
    $supplierProduct = StoreSupplierProduct::make()->action($supplier, SupplierProduct::factory()->definition());
    $this->assertModelExists($supplierProduct);

    return $supplierProduct;
})->depends('create supplier with agent');

test('create purchase order', function ($supplier) {
    $purchaseOrder = StorePurchaseOrder::make()->action($supplier, PurchaseOrder::factory()->definition());
    $this->assertModelExists($purchaseOrder);

    return $purchaseOrder;
})->depends('create supplier with agent');

test('check has one purchase order', function ($supplier) {
    $this->assertEquals(1, $supplier->stats->number_purchase_orders);
})->depends('create supplier with agent');

test('create new purchase order', function ($supplier) {
    expect(function () use ($supplier) {
        StorePurchaseOrder::make()->action($supplier, PurchaseOrder::factory()->definition());
    })->toThrow(ValidationException::class);
})->depends('create supplier with agent');

test('create new purchase order by force', function ($supplier) {
    $purchaseOrder = StorePurchaseOrder::make()->action($supplier, PurchaseOrder::factory()->definition(), true);
    $this->assertModelExists($purchaseOrder);

    return $purchaseOrder->fresh();
})->depends('create supplier with agent');

test('order', function ($supplier) {
    $this->assertEquals(1, $supplier->stats->number_purchase_orders);
})->depends('create supplier with agent');

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

test('create trade unit', function () {
    $tradeUnit = StoreTradeUnit::make()->action(TradeUnit::factory()->definition());
    $this->assertModelExists($tradeUnit);

    return $tradeUnit;
});

test('sync supplier product and trade units', function ($supplier) {
    $syncSupplierProductTradeUnit = SyncSupplierProductTradeUnits::run($supplier, [1]);
    $this->assertModelExists($syncSupplierProductTradeUnit);
})->depends('create supplier product');

test('update purchase order', function ($agent) {
    $purchaseOrder = UpdatePurchaseOrder::make()->action($agent, PurchaseOrder::factory()->definition());
    $this->assertModelExists($purchaseOrder);
})->depends('create new purchase order by force');

test('create purchase order by agent', function ($agent) {
    $purchaseOrder = StorePurchaseOrder::make()->action($agent, PurchaseOrder::factory()->definition());
    $this->assertModelExists($purchaseOrder);
})->depends('create agent');

test('change state to submit purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToSubmittedPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::SUBMITTED);
})->depends('create new purchase order by force');

test('change state to confirm purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToConfirmPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::CONFIRMED);
})->depends('create new purchase order by force');

test('change state to manufactured purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToManufacturedPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::MANUFACTURED);
})->depends('create new purchase order by force');

test('change state to dispatched from manufacture purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToDispatchedPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::DISPATCHED);
})->depends('create new purchase order by force');

test('change state to received from dispatch purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToReceivedPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::RECEIVED);
})->depends('create new purchase order by force');

test('change state to checked from received purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToCheckedPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::CHECKED);
})->depends('create new purchase order by force');

test('change state to settled from checked purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToSettledPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::SETTLED);
})->depends('create new purchase order by force');

test('change state to checked from settled purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToCheckedPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::CHECKED);
})->depends('create new purchase order by force');

test('change state to received from checked purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToReceivedPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::RECEIVED);
})->depends('create new purchase order by force');

test('change state to dispatched from received purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToDispatchedPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::DISPATCHED);
})->depends('create new purchase order by force');

test('change state to manufactured from dispatched purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToManufacturedPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::MANUFACTURED);
})->depends('create new purchase order by force');

test('change state to confirmed from manufactured purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToConfirmPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::CONFIRMED);
})->depends('create new purchase order by force');

test('change state to submitted from confirmed purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToSubmittedPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::SUBMITTED);
})->depends('create new purchase order by force');

test('change state to creating from submitted purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToCreatingPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::CREATING);
})->depends('create new purchase order by force');
