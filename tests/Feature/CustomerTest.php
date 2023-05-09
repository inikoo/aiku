<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 08 May 2023 09:03:42 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Marketing\Shop\StoreShop;
use App\Actions\Sales\Customer\StoreCustomer;
use App\Enums\Sales\Customer\CustomerStatusEnum;
use App\Models\Helpers\Address;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use App\Models\Tenancy\Tenant;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();
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
