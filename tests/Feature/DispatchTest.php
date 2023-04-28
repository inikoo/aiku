<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 27 Apr 2023 16:51:52 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Dispatch\DeliveryNote\StoreDeliveryNote;
use App\Actions\Dispatch\DeliveryNote\UpdateDeliveryNote;
use App\Actions\Dispatch\DeliveryNoteItem\StoreDeliveryNoteItem;
use App\Actions\Dispatch\Shipment\StoreShipment;
use App\Actions\Dispatch\Shipment\UpdateShipment;
use App\Actions\Dispatch\Shipper\UpdateShipper;
use App\Actions\Sales\Order\StoreOrder;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Dispatch\Shipment;
use App\Models\Dispatch\Shipper;
use App\Models\Helpers\Address;
use App\Models\Sales\Order;
use App\Models\Tenancy\Tenant;
use App\Actions\Dispatch\Shipper\StoreShipper;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $this->tenant = Tenant::where('slug', 'agb')->first();
    $this->tenant->makeCurrent();

});

test('create shipper', function () {
    $shipper = StoreShipper::make()->action(Shipper::factory()->definition());
    $this->assertModelExists($shipper);

    return $shipper;
});

test('update shipper', function ($shipper) {
    $shipper = UpdateShipper::make()->action($shipper, Shipper::factory()->definition());

    $this->assertModelExists($shipper);
})->depends('create shipper');

test('create delivery note', function () {
    $order = Order::latest()->first();
    $address = Address::latest()->first();

    $deliveryNote = StoreDeliveryNote::make()->action($order, DeliveryNote::factory()->definition(), $address);
    $this->assertModelExists($deliveryNote);

    return $deliveryNote;
})->todo(); // Todo waiting order to be tested

test('update delivery note', function ($deliveryNote) {
    $deliveryNote = UpdateDeliveryNote::make()->action($deliveryNote, DeliveryNote::factory()->definition());
    $this->assertModelExists($deliveryNote);

    return $deliveryNote;
})->depends('create delivery note');

test('create delivery note item', function ($deliveryNote) {
    $shipment = StoreDeliveryNoteItem::make()->action($deliveryNote, Shipment::factory()->definition());

    $this->assertModelExists($shipment);
})->depends('create delivery note');

test('create shipment', function ($deliveryNote) {
    $shipment = StoreShipment::make()->action($deliveryNote, Shipment::factory()->definition());

    $this->assertModelExists($shipment);
})->depends('create delivery note');

test('update shipment', function ($deliveryNote) {
    $shipment = UpdateShipment::make()->action($deliveryNote, Shipment::factory()->definition());

    $this->assertModelExists($shipment);
})->depends('create shipment');
