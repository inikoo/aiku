<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 05 May 2023 16:55:25 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Marketing\Shop\StoreShop;
use App\Actions\Sales\Customer\StoreCustomer;
use App\Actions\Sales\Order\DeleteOrder;
use App\Actions\Sales\Order\StoreOrder;
use App\Actions\Sales\Order\SubmitOrder;
use App\Actions\Sales\Order\UnSubmitOrder;
use App\Actions\Sales\Order\UpdateOrder;
use App\Enums\Sales\Customer\CustomerStatusEnum;
use App\Enums\Sales\Order\OrderStateEnum;
use App\Models\Helpers\Address;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use App\Models\Sales\Order;
use App\Models\Tenancy\Tenant;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();
});

test('create shop, customer', function () {
    $shop = StoreShop::make()->action(Shop::factory()->definition());
    $this->assertModelExists($shop);
    expect($shop->serialReferences()->count())->toBe(2);

    $customer = StoreCustomer::make()->action(
        $shop,
        Customer::factory()->definition(),
        Address::factory()->definition()
    );
    $this->assertModelExists($customer);
    expect($customer->reference)->toBe('000001')
        ->and($customer->status)->toBe(CustomerStatusEnum::APPROVED);

    return [
        'shop'     => $shop,
        'customer' => $customer
    ];
});

test('create order', function ($customer) {
    $i = 0;
    do {
        $billingAddress  = Address::first();
        $shipmentAddress = Address::latest()->first();
        $order           = StoreOrder::make()->action($customer['customer'], Order::factory()->definition(), $billingAddress, $shipmentAddress);

        $this->assertModelExists($order);
        $i++;
    } while ($i < 5);

    return $order;
})->depends('create shop, customer');

test('update order', function ($order) {
    $order = UpdateOrder::make()->action($order, Order::factory()->definition());

    $this->assertModelExists($order);
})->depends('create order');
test('submit order', function ($order) {
    $order = SubmitOrder::make()->action($order);

    expect($order->state)->toEqual(OrderStateEnum::SUBMITTED);
})->depends('create order');

test('un submit order', function ($order) {
    $order = UnSubmitOrder::make()->action($order);

    expect($order->state)->toBe(OrderStateEnum::CREATING);
})->depends('create order');

test('delete order', function ($order) {
    $order = DeleteOrder::run($order);

    $this->assertSoftDeleted($order);
})->depends('create order');
