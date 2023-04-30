<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 26 Apr 2023 12:12:30 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace Tests\Feature;

use App\Actions\Marketing\Shop\StoreShop;
use App\Actions\Sales\Customer\StoreCustomer;
use App\Actions\Sales\Order\StoreOrder;
use App\Actions\Sales\Order\UpdateOrder;
use App\Actions\Sales\Transaction\StoreTransaction;
use App\Actions\Sales\Transaction\UpdateTransaction;
use App\Enums\Sales\Customer\CustomerStatusEnum;
use App\Models\Helpers\Address;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use App\Models\Sales\Order;
use App\Models\Sales\Transaction;
use App\Models\Tenancy\Tenant;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $this->tenant = Tenant::where('slug', 'agb')->first();
    $this->tenant->makeCurrent();
});

test('create shop', function () {
    $shop = StoreShop::make()->action(Shop::factory()->definition());
    $this->assertModelExists($shop);
    expect($shop->serialReferences()->count())->toBe(2);

    return $shop;
});

test('create customer', function ($shop) {
    $customer = StoreCustomer::make()->action(
        $shop,
        Customer::factory()->definition(),
        Address::factory()->definition()
    );
    $this->assertModelExists($customer);
    expect($customer->reference)->toBe('000001')
        ->and($customer->status)->toBe(CustomerStatusEnum::APPROVED);


    return $customer;
})->depends('create shop');

test('create other customer', function ($shop) {
    $customer = StoreCustomer::make()->action(
        $shop,
        Customer::factory()->definition(),
        Address::factory()->definition()
    );
    expect($customer->reference)->toBe('000002');

    return $customer;
})->depends('create shop');

test('create order', function ($customer) {
    $billingAddress  = Address::first();
    $shipmentAddress = Address::latest()->first();
    $order           = StoreOrder::make()->action($customer, Order::factory()->definition(), $billingAddress, $shipmentAddress);

    $this->assertModelExists($order);

    return $order;
})->depends('create customer');

test('update order', function ($order) {
    $order = UpdateOrder::make()->action($order, Order::factory()->definition());

    $this->assertModelExists($order);
})->depends('create order');

test('create transaction', function ($order) {
    $transaction = StoreTransaction::make()->action($order, Transaction::factory()->definition());

    $this->assertModelExists($transaction);

    return $transaction;
})->depends('create order');

test('update transaction', function ($transaction) {
    $order = UpdateTransaction::make()->action($transaction, Transaction::factory()->definition());

    $this->assertModelExists($order);
})->depends('create transaction');
