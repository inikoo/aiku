<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 08 May 2023 09:03:42 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Procurement\OrgSupplier\StoreOrgSupplier;
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
use App\Actions\Procurement\SupplierDelivery\StoreSupplierDelivery;
use App\Actions\Procurement\SupplierDelivery\UpdateStateToCheckedSupplierDelivery;
use App\Actions\Procurement\SupplierDelivery\UpdateStateToDispatchSupplierDelivery;
use App\Actions\Procurement\SupplierDelivery\UpdateStateToReceivedSupplierDelivery;
use App\Actions\Procurement\SupplierDelivery\UpdateStateToSettledSupplierDelivery;
use App\Actions\Procurement\SupplierDeliveryItem\StoreSupplierDeliveryItem;
use App\Actions\Procurement\SupplierDeliveryItem\StoreSupplierDeliveryItemBySelectedPurchaseOrderItem;
use App\Actions\Procurement\SupplierDeliveryItem\UpdateStateToCheckedSupplierDeliveryItem;
use App\Actions\Procurement\SupplierProduct\StoreSupplierProduct;
use App\Actions\SupplyChain\Supplier\StoreSupplier;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\SupplierDelivery\SupplierDeliveryStateEnum;
use App\Models\Procurement\OrgSupplier;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderItem;
use App\Models\Procurement\SupplierDeliveryItem;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\SupplierProduct;
use Illuminate\Validation\ValidationException;

beforeAll(function () {
    loadDB('test_base_database.dump');
});


beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->group        = group();
});


test('create independent supplier', function () {
    $supplier = StoreSupplier::make()->action(
        parent: $this->group,
        modelData: Supplier::factory()->definition()
    );

    expect($supplier)->toBeInstanceOf(Supplier::class)
        ->and($this->group->supplyChainStats->number_suppliers)->toBe(1)
        ->and($this->organisation->procurementStats->number_suppliers)->toBe(0);

    return $supplier;
});

test('attach supplier to organisation', function ($supplier) {
    $orgSupplier = StoreOrgSupplier::make()->action($this->organisation, $supplier);


    expect($orgSupplier)->toBeInstanceOf(OrgSupplier::class)
        ->and($this->organisation->procurementStats->number_suppliers)->toBe(1);

    return $orgSupplier;
})->depends('create independent supplier');

test('create purchase order while no products', function ($orgSupplier) {
    expect(function () use ($orgSupplier) {
        StorePurchaseOrder::make()->action($this->organisation, $orgSupplier, PurchaseOrder::factory()->definition());
    })->toThrow(ValidationException::class);
})->depends('attach supplier to organisation');


test('create supplier product', function ($supplier) {
    $arrayData = [
        'code' => 'ABC',
        'name' => 'ABC Product',
        'cost' => 200,
    ];

    $supplierProduct = StoreSupplierProduct::make()->action($supplier, $arrayData);
    expect($supplierProduct)->toBeInstanceOf(SupplierProduct::class)
        ->and($supplierProduct->supplier_id)->toBe($supplier->id)
        ->and($supplierProduct->code)->toBe($arrayData['code'])
        ->and($supplierProduct->name)->toBe($arrayData['name'])
        ->and($supplierProduct->cost)->toBeNumeric(200);
    $supplier->refresh();

    return $supplier;
})->depends('create independent supplier');


test('create purchase order independent supplier', function (Supplier $supplier, OrgSupplier $orgSupplier) {
    $purchaseOrderData = PurchaseOrder::factory()->definition();


    $purchaseOrder = StorePurchaseOrder::make()->action($this->organisation, $orgSupplier, $purchaseOrderData);

    expect($purchaseOrder)->toBeInstanceOf(PurchaseOrder::class)
        ->and($supplier->stats->number_purchase_orders)->toBe(1)
        ->and($purchaseOrder->parent_id)->toBe($orgSupplier->id)
        ->and($purchaseOrder->supplier_id)->toBe($supplier->id);


    return $purchaseOrder;
})->depends('create supplier product', 'attach supplier to organisation');

test('add items to purchase order', function ($purchaseOrder) {
    $i = 1;
    do {
        AddItemPurchaseOrder::make()->action($purchaseOrder, PurchaseOrderItem::factory()->definition());
        $i++;
    } while ($i <= 5);

    $purchaseOrder->load('items');
    expect($purchaseOrder->items()->count())->toBe(5);

    return $purchaseOrder;
})->depends('create purchase order independent supplier');


test('delete purchase order', function () {
    $supplier    = StoreSupplier::make()->action(
        parent: $this->group,
        modelData: Supplier::factory()->definition()
    );
    $orgSupplier = StoreOrgSupplier::make()->action($this->organisation, $supplier);
    StoreSupplierProduct::make()->action($supplier, SupplierProduct::factory()->definition());

    $purchaseOrder = StorePurchaseOrder::make()->action($this->organisation, $orgSupplier, PurchaseOrder::factory()->definition());
    $purchaseOrder->fresh();

    expect($supplier->stats->number_purchase_orders)->toBe(1)->and($purchaseOrder)->toBeInstanceOf(PurchaseOrder::class);
    $purchaseOrderDeleted = false;
    try {
        $purchaseOrderDeleted = DeletePurchaseOrder::make()->action($purchaseOrder);
    } catch (ValidationException) {
    }
    $supplier->refresh();

    expect($purchaseOrderDeleted)->toBeTrue()->and($supplier->stats->number_purchase_orders)->toBe(0);
});

test('update quantity items to 0 in purchase order', function ($purchaseOrder) {
    $item = $purchaseOrder->items()->first();

    $item = UpdatePurchaseOrderItemQuantity::make()->action($item, [
        'unit_quantity' => 0
    ]);

    $this->assertModelMissing($item);
    expect($purchaseOrder->items()->count())->toBe(4);
})->depends('add items to purchase order');

test('update quantity items in purchase order', function ($purchaseOrder) {
    $item = $purchaseOrder->items()->first();

    $item = UpdatePurchaseOrderItemQuantity::make()->action($item, [
        'unit_quantity' => 12
    ]);
    expect($item)->toBeInstanceOf(PurchaseOrderItem::class)->and($item->unit_quantity)->toBe(12);
})->depends('add items to purchase order');


test('update purchase order', function ($purchaseOrder) {
    $dataToUpdate  = [
        'number' => 'PO-12345bis',
    ];
    $purchaseOrder = UpdatePurchaseOrder::make()->action($purchaseOrder, $dataToUpdate);
    $this->assertModelExists($purchaseOrder);
})->depends('create purchase order independent supplier');

test('create purchase order by agent', function () {
    $purchaseOrder = StorePurchaseOrder::make()->action($this->organisation, $agent, PurchaseOrder::factory()->definition());
    $this->assertModelExists($purchaseOrder);
})->todo();

test('change state to submit purchase order', function ($purchaseOrder) {
    $purchaseOrder->refresh();
    try {
        $purchaseOrder = UpdateStateToSubmittedPurchaseOrder::make()->action($purchaseOrder);
    } catch (ValidationException) {
    }
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::SUBMITTED);

    return $purchaseOrder;
})->depends('add items to purchase order');

test('change state to confirm purchase order', function ($purchaseOrder) {
    try {
        $purchaseOrder = UpdateStateToConfirmPurchaseOrder::make()->action($purchaseOrder);
    } catch (ValidationException) {
    }
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::CONFIRMED);

    return $purchaseOrder;
})->depends('change state to submit purchase order');

test('change state to manufactured purchase order', function ($purchaseOrder) {
    try {
        $purchaseOrder = UpdateStateToManufacturedPurchaseOrder::make()->action($purchaseOrder);
    } catch (ValidationException) {
    }
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::MANUFACTURED);

    return $purchaseOrder;
})->depends('create purchase order independent supplier');

test('change state to dispatched from manufacture purchase order', function ($purchaseOrder) {
    try {
        $purchaseOrder = UpdateStateToDispatchedPurchaseOrder::make()->action($purchaseOrder);
    } catch (ValidationException) {
    }
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::DISPATCHED);

    return $purchaseOrder;
})->depends('change state to confirm purchase order');

test('change state to received from dispatch purchase order', function ($purchaseOrder) {
    try {
        $purchaseOrder = UpdateStateToReceivedPurchaseOrder::make()->action($purchaseOrder);
    } catch (ValidationException) {
    }
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::RECEIVED);

    return $purchaseOrder;
})->depends('change state to manufactured purchase order');

test('change state to checked from received purchase order', function ($purchaseOrder) {
    try {
        $purchaseOrder = UpdateStateToCheckedPurchaseOrder::make()->action($purchaseOrder);
    } catch (ValidationException) {
    }
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::CHECKED);

    return $purchaseOrder;
})->depends('change state to received from dispatch purchase order');

test('change state to settled from checked purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToSettledPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::SETTLED);

    return $purchaseOrder;
})->depends('change state to checked from received purchase order');

test('change state to checked from settled purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToCheckedPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::CHECKED);
})->depends('create purchase order independent supplier');

test('change state to received from checked purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToReceivedPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::RECEIVED);
})->depends('create purchase order independent supplier');

test('change state to dispatched from received purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToDispatchedPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::DISPATCHED);
})->depends('create purchase order independent supplier');

test('change state to manufactured from dispatched purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToManufacturedPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::MANUFACTURED);
})->depends('create purchase order independent supplier');

test('change state to confirmed from manufactured purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToConfirmPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::CONFIRMED);
})->depends('create purchase order independent supplier');

test('change state to submitted from confirmed purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToSubmittedPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::SUBMITTED);
})->depends('create purchase order independent supplier');

test('change state to creating from submitted purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToCreatingPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::CREATING);
})->depends('create purchase order independent supplier');

test('create supplier delivery', function ($supplier) {
    $arrayData = [
        'number' => 12345,
        'date'   => date('Y-m-d')
    ];

    $supplierDelivery = StoreSupplierDelivery::make()->action($this->organisation, $supplier, $arrayData);

    expect($supplierDelivery->parent_id)->toBe($supplier->id)
        ->and($supplierDelivery->number)->toBeNumeric($arrayData['number'])
        ->and($supplierDelivery->date)->toBe($arrayData['date']);

    return $supplierDelivery->fresh();
})->depends('create independent supplier');


test('create supplier delivery items', function ($supplierDelivery) {
    $supplier = StoreSupplierDeliveryItem::run($supplierDelivery, SupplierDeliveryItem::factory()->definition());

    expect($supplier->supplier_delivery_id)->toBe($supplierDelivery->id);

    return $supplier;
})->depends('create supplier delivery');


test('create supplier delivery items by selected purchase order', function ($supplierDelivery, $items) {
    $supplier = StoreSupplierDeliveryItemBySelectedPurchaseOrderItem::run($supplierDelivery, $items->pluck('id')->toArray());
    expect($supplier)->toBeArray();

    return $supplier;
})->depends('create supplier delivery', 'add items to purchase order');

test('change state to dispatch from creating supplier delivery', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToDispatchSupplierDelivery::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(SupplierDeliveryStateEnum::DISPATCHED);
})->depends('create supplier delivery');

test('change state to received from dispatch supplier delivery', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToReceivedSupplierDelivery::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(SupplierDeliveryStateEnum::RECEIVED);
})->depends('create supplier delivery');

test('change state to checked from dispatch supplier delivery', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToCheckedSupplierDelivery::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(SupplierDeliveryStateEnum::CHECKED);
})->depends('create supplier delivery');

test('change state to settled from checked supplier delivery', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToSettledSupplierDelivery::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(SupplierDeliveryStateEnum::SETTLED);
})->depends('create supplier delivery');

test('change state to checked from settled supplier delivery', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToCheckedSupplierDelivery::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(SupplierDeliveryStateEnum::CHECKED);
})->depends('create supplier delivery');

test('change state to received from checked supplier delivery', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToReceivedSupplierDelivery::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(SupplierDeliveryStateEnum::RECEIVED);
})->depends('create supplier delivery');

test('check supplier delivery items not correct', function ($supplierDeliveryItem) {
    $supplierDeliveryItem = UpdateStateToCheckedSupplierDeliveryItem::make()->action($supplierDeliveryItem, [
        'unit_quantity_checked' => 2
    ]);
    expect($supplierDeliveryItem->supplierDelivery->state)->toEqual(SupplierDeliveryStateEnum::RECEIVED);
})->depends('create supplier delivery items');

test('check supplier delivery items all correct', function ($supplierDeliveryItems) {
    foreach ($supplierDeliveryItems as $supplierDeliveryItem) {
        UpdateStateToCheckedSupplierDeliveryItem::make()->action($supplierDeliveryItem, [
            'unit_quantity_checked' => 6
        ]);
    }
    expect($supplierDeliveryItems[0]->supplierDelivery->fresh()->state)->toEqual(SupplierDeliveryStateEnum::CHECKED);
})->depends('create supplier delivery items by selected purchase order');
