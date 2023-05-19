<?php

namespace Tests\Feature;

use App\Actions\Dispatch\DeliveryNote\DeleteDeliveryNote;
use App\Actions\Dispatch\DeliveryNote\StoreDeliveryNote;
use App\Actions\Dispatch\DeliveryNote\UpdateDeliveryNote;
use App\Actions\Dispatch\DeliveryNoteItem\StoreDeliveryNoteItem;
use App\Actions\Dispatch\Shipment\ApiCalls\ApcGbCallShipperApi;
use App\Actions\Dispatch\Shipment\StoreShipment;
use App\Actions\Dispatch\Shipment\UpdateShipment;
use App\Actions\Dispatch\Shipper\StoreShipper;
use App\Actions\Dispatch\Shipper\UpdateShipper;
use App\Actions\Inventory\Stock\StoreStock;
use App\Actions\Marketing\ShippingZone\StoreShippingZone;
use App\Actions\Marketing\ShippingZone\UpdateShippingZone;
use App\Actions\Marketing\ShippingZoneSchema\StoreShippingZoneSchema;
use App\Actions\Marketing\ShippingZoneSchema\UpdateShippingZoneSchema;
use App\Actions\Marketing\Shop\StoreShop;
use App\Actions\Sales\Customer\StoreCustomer;
use App\Actions\Sales\Order\StoreOrder;
use App\Actions\Sales\Transaction\StoreTransaction;
use App\Actions\Tenancy\Group\StoreGroup;
use App\Actions\Tenancy\Tenant\StoreTenant;
use App\Enums\Dispatch\DeliveryNote\DeliveryNoteStateEnum;
use App\Enums\Dispatch\DeliveryNote\DeliveryNoteStatusEnum;
use App\Enums\Mail\Outbox\OutboxTypeEnum;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Dispatch\Shipment;
use App\Models\Dispatch\Shipper;
use App\Models\Helpers\Address;
use App\Models\Inventory\Stock;
use App\Models\Marketing\ShippingZone;
use App\Models\Marketing\ShippingZoneSchema;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use App\Models\Sales\Order;
use App\Models\Sales\Transaction;
use App\Models\Tenancy\Group;
use App\Models\Tenancy\Tenant;
use Throwable;

beforeAll(function () {
    loadDB('test_base_database.dump');
});


beforeEach(function () {
    $tenant = Tenant::first();
    if (!$tenant) {
        $group  = StoreGroup::make()->asAction(Group::factory()->definition());
        $tenant = StoreTenant::make()->action($group, Tenant::factory()->definition());
    }
    $tenant->makeCurrent();
});

test('create shipper', function () {
    $shipper = StoreShipper::make()->action(Shipper::factory()->definition());
    $this->assertModelExists($shipper);
    return $shipper;
});

test('update shipper', function () {
    $shipper = Shipper::latest()->first();
    $shipper = UpdateShipper::make()->action($shipper, Shipper::factory()->definition());
    $this->assertModelExists($shipper);
});

test('create delivery note', function () {
    try {
        $shop     = StoreShop::make()->action(Shop::factory()->definition());
        $customer = StoreCustomer::make()->action(
            $shop,
            Customer::factory()->definition(),
            Address::factory()->definition()
        );
        $order = StoreOrder::make()->action(
            $customer,
            Order::factory()->definition(),
            Address::make(),
            Address::make()
        );
        $deliveryNote = StoreDeliveryNote::make()->action($order, DeliveryNote::factory()->definition(), Address::make());
        $this->assertModelExists($deliveryNote);

    } catch (Throwable $e) {
        echo $e->getMessage();
        $deliveryNote = null;
    }
    return $deliveryNote;
});

test('update delivery note', function ($deliveryNote) {
    $updatedDeliveryNote = UpdateDeliveryNote::make()->action($deliveryNote, [
        'number' => 2321321,
        'state'  => DeliveryNoteStateEnum::PICKING,
        'status' => DeliveryNoteStatusEnum::DISPATCHED,
        'email'  => 'test@email.com',
        'phone'  => '+0246578658',
        'date'   => date('Y-m-d')
    ]);
    expect($updatedDeliveryNote->number)->toBe(2321321);
})->depends('create delivery note');

test('create delivery note item', function ($deliveryNote) {
    $shop     = StoreShop::make()->action(Shop::factory()->definition());
    try {
        $customer = StoreCustomer::make()->action(
            $shop,
            Customer::factory()->definition(),
            Address::factory()->definition()
        );
        $order = StoreOrder::make()->action(
            $customer,
            Order::factory()->definition(),
            Address::make(),
            Address::make()
        );
        $stock            = StoreStock::make()->action($customer, Stock::factory()->definition());
        $transaction      = StoreTransaction::make()->action($order, Transaction::factory()->definition());
        $deliveryNoteItem = StoreDeliveryNoteItem::make()->action($deliveryNote, [
            'delivery_note_id'  => $deliveryNote->id,
            'stock_id'          => $stock->id,
            'transaction_id'    => $transaction->id,
        ]);
        $this->assertModelExists($deliveryNoteItem);
    } catch (Throwable $e) {
        echo $e->getMessage();
        $deliveryNoteItem = null;
    }
    return $deliveryNoteItem;
})->depends('create delivery note');


test('remove delivery note', function ($deliveryNote) {
    $success = DeleteDeliveryNote::make()->handle($deliveryNote);
    $this->assertModelExists($deliveryNote);
    return $success;
})->depends('create delivery note', 'create delivery note item');


test('create shipment', function ($deliveryNote, $shipper) {
    $shipper['api_shipper'] = '';
    $shipment               = StoreShipment::make()->action($deliveryNote, $shipper, Shipment::factory()->definition());
    $this->assertModelExists($shipment);
    return $shipment;
})->depends('create delivery note', 'create shipper');

test('update shipment', function () {
    $shipment = Shipment::latest()->first();
    $shipment = UpdateShipment::make()->action($shipment, Shipment::factory()->definition());
    $this->assertModelExists($shipment);
})->depends('create shipment');

//test('apc gb call shipper api', function ($deliveryNote, $shipper) {
//    $responseJson = ApcGbCallShipperApi::make()->action($deliveryNote,$shipper);
//    return $responseJson['data'];
//})->depends('create delivery note','create shipper');

test('create shop', function () {
    $shop = StoreShop::make()->action(Shop::factory()->definition());
    $this->assertModelExists($shop);
    expect($shop->paymentAccounts()->count())->toBe(1)
        ->and($shop->outboxes()->count())->toBe(count(OutboxTypeEnum::values()));


    return $shop;
});

test('create shipping zone schema', function ($shop) {
    $shippingZoneSchema = StoreShippingZoneSchema::make()->action($shop, ShippingZoneSchema::factory()->definition());
    $this->assertModelExists($shop);

    return $shippingZoneSchema;
})->depends('create shop');

test('update shipping zone schema', function ($shippingZoneSchema) {
    $shippingZoneSchema = UpdateShippingZoneSchema::make()->action($shippingZoneSchema, ShippingZoneSchema::factory()->definition());
    $this->assertModelExists($shippingZoneSchema);
})->depends('create shipping zone schema');

test('create shipping zone', function ($shippingZoneSchema) {
    $shippingZone = StoreShippingZone::make()->action($shippingZoneSchema, ShippingZone::factory()->definition());
    $this->assertModelExists($shippingZoneSchema);

    return $shippingZone;
})->depends('create shipping zone schema');

test('update shipping zone', function ($shippingZone) {
    $shippingZone = UpdateShippingZone::make()->action($shippingZone, ShippingZone::factory()->definition());
    $this->assertModelExists($shippingZone);
})->depends('create shipping zone');
