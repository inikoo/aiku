<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Jan 2024 12:57:17 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\CRM\Customer\StoreCustomer;
use App\Actions\Dispatch\DeliveryNote\DeleteDeliveryNote;
use App\Actions\Dispatch\DeliveryNote\StoreDeliveryNote;
use App\Actions\Dispatch\DeliveryNote\UpdateDeliveryNote;
use App\Actions\Dispatch\DeliveryNoteItem\StoreDeliveryNoteItem;
use App\Actions\Dispatch\Shipment\StoreShipment;
use App\Actions\Dispatch\Shipment\UpdateShipment;
use App\Actions\Dispatch\Shipper\StoreShipper;
use App\Actions\Dispatch\Shipper\UpdateShipper;
use App\Actions\Dispatch\ShippingEvent\StoreShippingEvent;
use App\Actions\Dispatch\ShippingEvent\UpdateShippingEvent;
use App\Actions\Goods\Stock\StoreStock;
use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\Inventory\Warehouse\StoreWarehouse;
use App\Actions\Ordering\Order\StoreOrder;
use App\Actions\Ordering\Transaction\StoreTransaction;
use App\Enums\Dispatch\DeliveryNote\DeliveryNoteStateEnum;
use App\Enums\Dispatch\DeliveryNote\DeliveryNoteStatusEnum;
use App\Enums\Mail\Outbox\OutboxTypeEnum;
use App\Models\CRM\Customer;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Dispatch\Shipment;
use App\Models\Dispatch\ShippingEvent;
use App\Models\Helpers\Address;
use App\Models\Catalogue\Shop;
use App\Models\Ordering\Transaction;
use App\Models\SupplyChain\Stock;
use Throwable;

beforeAll(function () {
    loadDB('test_base_database.dump');
});

beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->group        = $this->organisation->group;

    $warehouse = $this->organisation->warehouses()->first();
    if (!$warehouse) {
        $warehouse = StoreWarehouse::make()->action(
            $this->organisation,
            [
                'code' => 'ts12',
                'name' => 'testName',
            ]
        );
    }
    $this->warehouse=$warehouse;

});

test('create shipper', function () {
    $arrayData = [
        'code' => 'ABC',
        'name' => 'ABC Shipping'
    ];

    $createdShipper = StoreShipper::make()->action($this->organisation, $arrayData);
    expect($createdShipper->code)->toBe($arrayData['code']);

    return $createdShipper;
});

test('update shipper', function ($createdShipper) {
    $arrayData = [
        'code' => 'DEF',
        'name' => 'DEF Movers'
    ];

    $updatedShipper = UpdateShipper::make()->action($createdShipper, $arrayData);

    expect($updatedShipper->code)->toBe($arrayData['code']);
})->depends('create shipper');

test('create shop', function () {
    $shop = StoreShop::make()->action($this->organisation, Shop::factory()->definition());

    expect($shop->paymentAccounts()->count())->toBe(1)
        ->and($shop->outboxes()->count())->toBe(
            count(OutboxTypeEnum::values()) - 1
        );

    return $shop;
});

test('create customer', function ($shop) {
    $createdCustomer = StoreCustomer::make()->action($shop, Customer::factory()->definition());
    expect($createdCustomer)->toBeInstanceOf(Customer::class);

    return $createdCustomer;
})->depends('create shop');

test('create order', function ($createdCustomer) {
    $arrayData = [
        'number'           => '123456',
        'date'             => date('Y-m-d'),
        'customer_id'      => $createdCustomer->id,
        'delivery_address' => new Address(Address::factory()->definition()),
        'billing_address'  => new Address(Address::factory()->definition())
    ];

    $createdOrder = StoreOrder::make()->action($createdCustomer, $arrayData);

    expect($createdOrder->number)->toBe($arrayData['number']);

    return $createdOrder;
})->depends('create customer');

test('create delivery note', function ($createdOrder) {
    $arrayData = [
        'number'           => 'A123456',
        'state'            => DeliveryNoteStateEnum::SUBMITTED,
        'status'           => DeliveryNoteStatusEnum::HANDLING,
        'email'            => 'test@email.com',
        'phone'            => '+62081353890000',
        'date'             => date('Y-m-d'),
        'delivery_address' => new Address(Address::factory()->definition()),
        'warehouse_id'     => $this->warehouse->id
    ];

    $deliveryNote = StoreDeliveryNote::make()->action($createdOrder, $arrayData);
    expect($deliveryNote)->toBeInstanceOf(DeliveryNote::class)
        ->and($deliveryNote->number)->toBe($arrayData['number']);


    return $deliveryNote;
})->depends('create order');

test('update delivery note', function ($lastDeliveryNote) {
    $arrayData = [
        'number' => 'A2321321',
        'state'  => DeliveryNoteStateEnum::PICKING,
        'status' => DeliveryNoteStatusEnum::DISPATCHED,
        'email'  => 'test@email.com',
        'phone'  => '+62081353890000',
        'date'   => date('Y-m-d')
    ];

    $updatedDeliveryNote = UpdateDeliveryNote::make()->action($lastDeliveryNote, $arrayData);

    expect($updatedDeliveryNote->number)->toBe($arrayData['number']);
})->depends('create delivery note');

test('create delivery note item', function ($customer, $order, $deliveryNote) {
    try {
        $stock       = StoreStock::make()->action($this->group, Stock::factory()->definition());
        $transaction = StoreTransaction::make()->action($order, Transaction::factory()->definition());

        $deliveryNoteData = [
            'delivery_note_id' => $deliveryNote->id,
            'stock_id'         => $stock->id,
            'transaction_id'   => $transaction->id,
        ];

        $deliveryNoteItem = StoreDeliveryNoteItem::make()->action($deliveryNote, $deliveryNoteData);

        expect($deliveryNoteItem->delivery_note_id)->toBe($deliveryNoteData['delivery_note_id']);
    } catch (Throwable $e) {
        echo $e->getMessage();
        $deliveryNoteItem = null;
    }

    return $deliveryNoteItem;
})->depends('create customer', 'create order', 'create delivery note')->todo();


test('remove delivery note', function ($deliveryNote) {
    $success = DeleteDeliveryNote::make()->handle($deliveryNote);

    $this->assertModelExists($deliveryNote);

    return $success;
})->depends('create delivery note', 'create delivery note item');


test('create shipment', function ($deliveryNote, $shipper) {
    $arrayData              = [
        'reference' => 'AAA'
    ];
    $shipper['api_shipper'] = '';

    $shipment = StoreShipment::make()->action($deliveryNote, $shipper, $arrayData);
    expect($shipment)->toBeInstanceOf(Shipment::class)
        ->and($shipment->reference)->toBe($arrayData['reference']);

    return $shipment;
})->depends('create delivery note', 'create shipper');

test('update shipment', function ($lastShipment) {
    $arrayData = [
        'reference' => 'BBB'
    ];

    $shipment = UpdateShipment::make()->action($lastShipment, $arrayData);

    expect($shipment->reference)->toBe($arrayData['reference']);
})->depends('create shipment');

/*
test('create shipping event', function ($deliveryNote, $shipper) {

    $arrayData = [
        'events' => [
            'state' => 'in-process'
        ]
    ];

    $shippingEvent = StoreShippingEvent::make()->action($shipper, $arrayData);
    expect($shippingEvent)->toBeInstanceOf(ShippingEvent::class);

    return $shippingEvent;
})->depends('create shipper');

test('update shipping event', function ($shippingEvent) {
    $arrayData = [
        'events' => [
            'state' => 'delivered'
        ]
    ];

    $shippingEvent = UpdateShippingEvent::make()->action($shippingEvent, $arrayData);

    expect($shippingEvent->events)->toBe($arrayData['events']);
})->depends('create shipping event');*/
