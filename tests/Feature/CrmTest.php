<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 07 Jun 2024 11:18:49 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\CRM\Customer\StoreCustomer;
use App\Actions\CRM\CustomerClient\StoreCustomerClient;
use App\Actions\CRM\CustomerClient\UpdateCustomerClient;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\CRM\Customer\CustomerStatusEnum;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Customer;
use App\Models\CRM\CustomerClient;

beforeAll(function () {
    loadDB('test_base_database.dump');
});

beforeEach(function () {
    list(
        $this->organisation,
        $this->user,
        $this->shop
    ) = createShop();

    if (!isset($this->dropshippingShop)) {
        $storeData    = Shop::factory()->definition();
        data_set($storeData, 'type', ShopTypeEnum::DROPSHIPPING);

        $this->dropshippingShop = StoreShop::make()->action(
            $this->organisation,
            $storeData
        );
    }

});


test('create customer', function () {
    $customer = StoreCustomer::make()->action(
        $this->shop,
        Customer::factory()->definition(),
    );
    $customer->refresh();

    expect($customer)->toBeInstanceOf(Customer::class)
        ->and($customer->reference)->toBe('000001')
        ->and($customer->status)->toBe(CustomerStatusEnum::APPROVED)
        ->and($customer->group->crmStats->number_customers)->toBe(1)
        ->and($customer->organisation->crmStats->number_customers)->toBe(1)
        ->and($customer->shop->crmStats->number_customers)->toBe(1)
    ;

    return $customer;
});

test('create other customer', function () {
    try {
        $customer = StoreCustomer::make()->action(
            $this->shop,
            Customer::factory()->definition()
        );
    } catch (Throwable) {
        $customer = null;
    }
    expect($customer)->toBeInstanceOf(Customer::class)
        ->and($customer->reference)->toBe('000002')
        ->and($customer->status)->toBe(CustomerStatusEnum::APPROVED);

    return $customer;
});



test('create customer client', function () {
    $shop           = StoreShop::make()->action($this->organisation, Shop::factory()->definition());
    $customer       = StoreCustomer::make()->action($shop, Customer::factory()->definition());
    $customerClient = StoreCustomerClient::make()->action($customer, CustomerClient::factory()->definition());
    $this->assertModelExists($customerClient);
    expect($customerClient->shop->code)->toBe($shop->code)
        ->and($customerClient->customer->reference)->toBe($customer->reference);

    return $customerClient;
});

test('update customer client', function ($customerClient) {
    $customerClient = UpdateCustomerClient::make()->action($customerClient, ['reference' => '001']);
    expect($customerClient->reference)->toBe('001');
})->depends('create customer client');
