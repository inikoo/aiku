<?php

namespace Tests\Feature;

use App\Actions\Dispatch\DeliveryNote\StoreDeliveryNote;
use App\Actions\Dispatch\DeliveryNote\UpdateDeliveryNote;
use App\Actions\Dispatch\DeliveryNoteItem\StoreDeliveryNoteItem;
//use App\Actions\Dispatch\Shipment\StoreShipment;
use App\Actions\Dispatch\Shipper\StoreShipper;
use App\Actions\Dispatch\Shipper\UpdateShipper;
use App\Actions\Inventory\Stock\StoreStock;
use App\Actions\Marketing\Shop\StoreShop;
use App\Actions\Sales\Customer\StoreCustomer;
use App\Actions\Sales\Order\StoreOrder;
use App\Actions\Sales\Transaction\StoreTransaction;
use App\Actions\Tenancy\Group\StoreGroup;
use App\Actions\Tenancy\Tenant\StoreTenant;
use App\Enums\Dispatch\DeliveryNote\DeliveryNoteStateEnum;
use App\Enums\Dispatch\DeliveryNote\DeliveryNoteStatusEnum;
use App\Models\Dispatch\DeliveryNote;
//use App\Models\Dispatch\Shipment;
use App\Models\Dispatch\Shipper;
use App\Models\Helpers\Address;
use App\Models\Inventory\Stock;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use App\Models\Sales\Order;
use App\Models\Sales\Transaction;
use App\Models\Tenancy\Tenant;
use Throwable;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();
});

test('create shipper', function () {
    $shipper = StoreShipper::make()->action(Shipper::factory()->definition());
    $this->assertModelExists($shipper);
    return $shipper;
});

test('update shipper', function () {
    $shipper = Shipper::latest()->first();
    $shipper = UpdateShipper::run($shipper, Shipper::factory()->definition());
    $this->assertModelExists($shipper);
});

test('create delivery note',function () {
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
    $deliveryNote = UpdateDeliveryNote::make()->action($deliveryNote, [
        'number' => 2321321,
        'state'  => DeliveryNoteStateEnum::PICKING,
        'status' => DeliveryNoteStatusEnum::DISPATCHED,
        'email'  => 'test@email.com',
        'phone'  => '+0246578658',
        'date'   => date('Y-m-d')
    ]);
    expect($deliveryNote->number)->toBe(2321321);
})->depends('create delivery note');

test('create delivery note item', function ($deliveryNote) {
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
    $stock = StoreStock::make()->action($customer, Stock::factory()->definition());
    $transaction = StoreTransaction::make()->action($order, Transaction::factory()->definition());
    $deliveryNoteItem = StoreDeliveryNoteItem::make()->action($deliveryNote,[
        'delivery_note_id'  => $deliveryNote->id,
        'stock_id'          => $stock->id,
        'transaction_id'    => $transaction->id,
    ]);
    $this->assertModelExists($deliveryNoteItem);
    return $deliveryNoteItem;
})->depends('create delivery note');

//test('delete delivery note item', function ($deliveryNote) {
//    $delnote = DeleteDeliveryNote::run($deliveryNote);
//    $this->assertModelExists($delnote);
//})->depends('create delivery note');

//test('create shipment', function ($deliveryNote) {
//    $shipper = StoreShipper::make()->action(Shipper::factory()->definition());
//    $shipment = StoreShipment::make()->action($deliveryNote, $shipper,Shipment::factory()->definition());
//    $this->assertModelExists($shipment);
//    return $shipment;
//})->depends('create delivery note');

//test('update shipment', function () {
//    $shipper = Shipper::latest()->first();
//    $shipper = UpdateShipper::run($shipper, Shipper::factory()->definition());
//    $this->assertModelExists($shipper);
//});
