<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 08 May 2023 10:58:47 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Dispatch\DeliveryNote\StoreDeliveryNote;
use App\Actions\Dispatch\Shipment\StoreShipment;
use App\Actions\Dispatch\Shipment\UpdateShipment;
use App\Actions\Inventory\Stock\StoreStock;
use App\Actions\Marketing\Shop\StoreShop;
use App\Actions\Sales\Customer\StoreCustomer;
use App\Actions\Sales\Order\StoreOrder;
use App\Actions\Sales\Transaction\StoreTransaction;
use App\Enums\Sales\Customer\CustomerStatusEnum;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Dispatch\Shipment;
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

    $billingAddress = Address::first();
    $shipmentAddress = Address::latest()->first();
    $order = StoreOrder::make()->action($customer, Order::factory()->definition(), $billingAddress, $shipmentAddress);

    $stock = StoreStock::run(app('currentTenant'), Stock::factory()->definition());

    $transaction = StoreTransaction::make()->action($order, Transaction::factory()->definition());

    $address = Address::latest()->first();

    $deliveryNote = StoreDeliveryNote::make()->action($order, DeliveryNote::factory()->definition(), $address);

    return [
        'shop' => $shop,
        'customer' => $customer,
        'order' => $order,
        'stock' => $stock,
        'transaction' => $transaction,
        'delivery_note' => $deliveryNote
    ];
});

test('create shipment', function ($deliveryNote) {
    $shipment = StoreShipment::make()->action($deliveryNote['delivery_note'], Shipment::factory()->definition());

    $this->assertModelExists($shipment);

    return $shipment;
})->depends('create shop, customer, order');

test('update shipment', function ($shipment) {
    $shipment = UpdateShipment::make()->action($shipment, Shipment::factory()->definition());

    $this->assertModelExists($shipment);
})->depends('create shipment');
