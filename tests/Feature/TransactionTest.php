<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 05 May 2023 16:55:25 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Marketing\Shop\StoreShop;
use App\Actions\Sales\Customer\StoreCustomer;
use App\Actions\Sales\Order\StoreOrder;
use App\Actions\Sales\Transaction\StoreTransaction;
use App\Actions\Sales\Transaction\UpdateTransaction;
use App\Actions\Tenancy\Group\StoreGroup;
use App\Actions\Tenancy\Tenant\StoreTenant;
use App\Enums\Sales\Customer\CustomerStatusEnum;
use App\Models\Helpers\Address;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use App\Models\Sales\Order;
use App\Models\Sales\Transaction;
use App\Models\Tenancy\Group;
use App\Models\Tenancy\Tenant;

beforeAll(function () {
    loadDB('d1_fresh_with_assets.dump');
});


beforeEach(function () {
    $tenant = Tenant::first();
    if (!$tenant) {
        $group  = StoreGroup::make()->asAction(Group::factory()->definition());
        $tenant = StoreTenant::make()->action($group, Tenant::factory()->definition());
    }
    $tenant->makeCurrent();
});

test('create order', function () {
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

    $billingAddress  = Address::first();
    $shipmentAddress = Address::latest()->first();
    $order           = StoreOrder::make()->action($customer, Order::factory()->definition(), $billingAddress, $shipmentAddress);

    $this->assertModelExists($order);

    return $order;
});

test('create transaction', function ($order) {
    $transaction = StoreTransaction::make()->action($order, Transaction::factory()->definition());

    $this->assertModelExists($transaction);

    return $transaction;
})->depends('create order');

test('update transaction', function ($transaction) {
    $order = UpdateTransaction::make()->action($transaction, Transaction::factory()->definition());

    $this->assertModelExists($order);
})->depends('create transaction');
