<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 05 May 2023 16:55:25 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Marketing\Shop\StoreShop;
use App\Actions\Sales\Customer\StoreCustomer;
use App\Actions\Sales\Order\DeleteOrder;
use App\Actions\Sales\Order\StoreOrder;
use App\Actions\Sales\Order\UpdateOrder;
use App\Actions\Sales\Order\UpdateStateToCreatingOrder;
use App\Actions\Sales\Order\UpdateStateToFinalizedOrder;
use App\Actions\Sales\Order\UpdateStateToHandlingOrder;
use App\Actions\Sales\Order\UpdateStateToPackedOrder;
use App\Actions\Sales\Order\UpdateStateToSettledOrder;
use App\Actions\Sales\Order\UpdateStateToSubmittedOrder;
use App\Actions\Tenancy\Group\StoreGroup;
use App\Actions\Tenancy\Tenant\StoreTenant;
use App\Enums\Sales\Customer\CustomerStatusEnum;
use App\Enums\Sales\Order\OrderStateEnum;
use App\Models\Helpers\Address;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use App\Models\Sales\Order;
use App\Models\Tenancy\Group;
use App\Models\Tenancy\Tenant;

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

test('update state to submit from creating order', function ($order) {
    $order = UpdateStateToSubmittedOrder::make()->action($order);

    expect($order->state)->toEqual(OrderStateEnum::SUBMITTED);
})->depends('create order');

test('update state to handling from submit order', function ($order) {
    $order = UpdateStateToHandlingOrder::make()->action($order);

    expect($order->state)->toEqual(OrderStateEnum::HANDLING);
})->depends('create order');

test('update state to packed from handling', function ($order) {
    $order = UpdateStateToPackedOrder::make()->action($order);

    expect($order->state)->toBe(OrderStateEnum::PACKED);
})->depends('create order');

test('update state to finalized from handling', function ($order) {
    $order = UpdateStateToFinalizedOrder::make()->action($order);

    expect($order->state)->toBe(OrderStateEnum::FINALISED);
})->depends('create order');

test('update state to settled from finalized', function ($order) {
    $order = UpdateStateToSettledOrder::make()->action($order);

    expect($order->state)->toBe(OrderStateEnum::SETTLED);
})->depends('create order');

test('update state to finalized from settled', function ($order) {
    $order = UpdateStateToFinalizedOrder::make()->action($order);

    expect($order->state)->toBe(OrderStateEnum::FINALISED);
})->depends('create order');

test('update state to packed from finalized', function ($order) {
    $order = UpdateStateToPackedOrder::make()->action($order);

    expect($order->state)->toBe(OrderStateEnum::PACKED);
})->depends('create order');

test('update state to handling from packed', function ($order) {
    $order = UpdateStateToHandlingOrder::make()->action($order);

    expect($order->state)->toEqual(OrderStateEnum::HANDLING);
})->depends('create order');

test('update state to submit from handling', function ($order) {
    $order = UpdateStateToSubmittedOrder::make()->action($order);

    expect($order->state)->toEqual(OrderStateEnum::SUBMITTED);
})->depends('create order');

test('update state to creating from submitted', function ($order) {
    $order = UpdateStateToCreatingOrder::make()->action($order);

    expect($order->state)->toEqual(OrderStateEnum::CREATING);
})->depends('create order');

test('delete order', function ($order) {
    $order = DeleteOrder::run($order);

    $this->assertSoftDeleted($order);
})->depends('create order');
