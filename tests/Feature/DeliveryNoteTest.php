<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 08 May 2023 09:51:49 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Dispatch\DeliveryNote\StoreDeliveryNote;
use App\Actions\Dispatch\DeliveryNote\UpdateDeliveryNote;
use App\Actions\Dispatch\DeliveryNoteItem\StoreDeliveryNoteItem;
use App\Actions\Inventory\Stock\StoreStock;
use App\Actions\Marketing\Shop\StoreShop;
use App\Actions\Sales\Customer\StoreCustomer;
use App\Actions\Sales\Order\StoreOrder;
use App\Actions\Sales\Transaction\StoreTransaction;
use App\Enums\Sales\Customer\CustomerStatusEnum;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Helpers\Address;
use App\Models\Inventory\Stock;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use App\Models\Sales\Order;
use App\Models\Sales\Transaction;
use App\Models\Tenancy\Tenant;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();
});

test('create shop, customer, order', function () {
    $shop = StoreShop::make()->action(Shop::factory()->definition());
    expect($shop->serialReferences()->count())->toBe(2);

    $customer = StoreCustomer::make()->action(
        $shop,
        Customer::factory()->definition(),
        Address::factory()->definition()
    );

    expect($customer->reference)->toBe('000001')
        ->and($customer->status)->toBe(CustomerStatusEnum::APPROVED);

    $billingAddress  = Address::first();
    $shipmentAddress = Address::latest()->first();
    $order           = StoreOrder::make()->action($customer, Order::factory()->definition(), $billingAddress, $shipmentAddress);

    $stock = StoreStock::run(app('currentTenant'), Stock::factory()->definition());

    $transaction = StoreTransaction::make()->action($order, Transaction::factory()->definition());

    return [
        'shop'        => $shop,
        'customer'    => $customer,
        'order'       => $order,
        'stock'       => $stock,
        'transaction' => $transaction
    ];
});

test('create delivery note', function ($order) {
    $address = Address::latest()->first();

    $deliveryNote = StoreDeliveryNote::make()->action($order['order'], DeliveryNote::factory()->definition(), $address);
    $this->assertModelExists($deliveryNote);

    return $deliveryNote;
})->depends('create shop, customer, order');

test('update delivery note', function ($deliveryNote) {
    $deliveryNote = UpdateDeliveryNote::make()->action($deliveryNote, DeliveryNote::factory()->definition());
    $this->assertModelExists($deliveryNote);

    return $deliveryNote;
})->depends('create delivery note');

test('create delivery note item', function ($deliveryNote, $stock) {
    $shipment = StoreDeliveryNoteItem::make()->action($deliveryNote, [
        'stock_id'       => $stock['stock']->id,
        'transaction_id' => $stock['transaction']->id
    ]);

    $this->assertModelExists($shipment);
})->depends('create delivery note', 'create shop, customer, order');
