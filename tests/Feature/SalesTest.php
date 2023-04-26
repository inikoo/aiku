<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 26 Apr 2023 12:12:30 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace Tests\Feature;

use App\Actions\Marketing\Shop\StoreShop;
use App\Actions\Sales\Customer\StoreCustomer;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use App\Models\Tenancy\Tenant;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $this->tenant = Tenant::where('slug', 'agb')->first();
    $this->tenant->makeCurrent();

});

test('create shop', function () {
    $shop = StoreShop::make()->action(Shop::factory()->definition());
    $this->assertModelExists($shop);
    return $shop;
});

test('create customer', function ($shop) {
    $customer = StoreCustomer::make()->action($shop, Customer::factory()->definition());
    $this->assertModelExists($customer);
    return $customer;
})->depends('create shop');
