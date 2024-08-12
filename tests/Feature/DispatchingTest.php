<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Jan 2024 12:57:17 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Dispatching\DeliveryNote\DeleteDeliveryNote;
use App\Actions\Dispatching\DeliveryNote\StoreDeliveryNote;
use App\Actions\Dispatching\DeliveryNote\UpdateDeliveryNote;
use App\Actions\Dispatching\DeliveryNoteItem\StoreDeliveryNoteItem;
use App\Actions\Dispatching\Shipment\StoreShipment;
use App\Actions\Dispatching\Shipment\UpdateShipment;
use App\Actions\Dispatching\Shipper\StoreShipper;
use App\Actions\Dispatching\Shipper\UpdateShipper;
use App\Actions\Dispatching\ShippingEvent\StoreShippingEvent;
use App\Actions\Dispatching\ShippingEvent\UpdateShippingEvent;
use App\Actions\Goods\Stock\StoreStock;
use App\Actions\Ordering\Transaction\StoreTransaction;
use App\Enums\Dispatching\DeliveryNote\DeliveryNoteStateEnum;
use App\Enums\Dispatching\DeliveryNote\DeliveryNoteStatusEnum;
use App\Models\Dispatching\DeliveryNote;
use App\Models\Dispatching\Shipment;
use App\Models\Dispatching\ShippingEvent;
use App\Models\Helpers\Address;
use App\Models\Ordering\Transaction;
use App\Models\SupplyChain\Stock;
use Throwable;

beforeAll(function () {
    loadDB();
});

beforeEach(function () {


    list(
        $this->organisation,
        $this->user,
        $this->shop
    ) = createShop();

    $this->group        = $this->organisation->group;

    $this->warehouse= createWarehouse();

    list(
        $this->tradeUnit,
        $this->product
    )=createProduct($this->shop);

    $this->customer=createCustomer($this->shop);
    $this->order   =createOrder($this->customer, $this->product);


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



test('create delivery note', function () {
    $arrayData = [
        'reference'           => 'A123456',
        'state'               => DeliveryNoteStateEnum::SUBMITTED,
        'status'              => DeliveryNoteStatusEnum::HANDLING,
        'email'               => 'test@email.com',
        'phone'               => '+62081353890000',
        'date'                => date('Y-m-d'),
        'delivery_address'    => new Address(Address::factory()->definition()),
        'warehouse_id'        => $this->warehouse->id
    ];

    $deliveryNote = StoreDeliveryNote::make()->action($this->order, $arrayData);
    expect($deliveryNote)->toBeInstanceOf(DeliveryNote::class)
        ->and($deliveryNote->reference)->toBe($arrayData['reference']);


    return $deliveryNote;
});

test('update delivery note', function ($lastDeliveryNote) {
    $arrayData = [
        'reference' => 'A2321321',
        'state'     => DeliveryNoteStateEnum::PICKING,
        'status'    => DeliveryNoteStatusEnum::DISPATCHED,
        'email'     => 'test@email.com',
        'phone'     => '+62081353890000',
        'date'      => date('Y-m-d')
    ];

    $updatedDeliveryNote = UpdateDeliveryNote::make()->action($lastDeliveryNote, $arrayData);

    expect($updatedDeliveryNote->reference)->toBe($arrayData['reference']);
})->depends('create delivery note');

test('create delivery note item', function (DeliveryNote $deliveryNote) {
    try {
        $stock       = StoreStock::make()->action($this->group, Stock::factory()->definition());
        $transaction = StoreTransaction::make()->action($this->order, Transaction::factory()->definition());

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
})->depends('create delivery note')->todo();


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
