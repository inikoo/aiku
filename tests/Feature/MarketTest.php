<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 02 Jan 2024 22:04:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Market\Shop\StoreShop;
use App\Actions\Market\Shop\UpdateShop;
use App\Enums\Market\Shop\ShopTypeEnum;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Permission;
use App\Models\SysAdmin\Role;
use App\Models\Web\Website;

beforeAll(function () {
    loadDB('test_base_database.dump');
});


beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->guest        = createAdminGuest($this->organisation->group);
    $this->warehouse    = createWarehouse();
});

test('create shop', function () {
    $organisation = $this->organisation;
    $storeData    = Shop::factory()->definition();
    data_set($storeData, 'type', ShopTypeEnum::B2B->value);
    $shop = StoreShop::make()->action($this->organisation, $storeData);
    $organisation->refresh();

    $shopRoles       = Role::where('scope_type', 'Shop')->where('scope_id', $shop->id)->get();
    $shopPermissions = Permission::where('scope_type', 'Shop')->where('scope_id', $shop->id)->get();

    expect($shop)->toBeInstanceOf(Shop::class)
        ->and($organisation->marketStats->number_shops)->toBe(1)
        ->and($organisation->marketStats->number_shops_type_b2b)->toBe(1)
        ->and($shopRoles->count())->toBe(3)
        ->and($shopPermissions->count())->toBe(23);


    $user = $this->guest->user;
    $user->refresh();

    expect($user->getAllPermissions()->count())->toBe(26)
        ->and($user->hasAllRoles(["shop-admin-$shop->id"]))->toBe(true);


    return $shop;
});

test('create shop by command', function () {
    $organisation = $this->organisation;
    $this->artisan('shop:create', [
        'organisation'   => $organisation->slug,
        'name'           => 'Test Shop',
        'code'           => 'TEST',
        'type'           => ShopTypeEnum::FULFILMENT->value,
        '--warehouses'   => [$this->warehouse->id]
    ])->assertExitCode(0);
    $organisation->refresh();

    expect($organisation->marketStats->number_shops)->toBe(2)
        ->and($organisation->marketStats->number_shops_type_b2b)->toBe(1)
        ->and($organisation->marketStats->number_shops_type_fulfilment)->toBe(1);
})->depends('create shop');

test('update shop', function (Shop $shop) {
    $updateData = [
        'name' => 'Test Shop Updated',
    ];

    $shop = UpdateShop::make()->action($shop, $updateData);
    $shop->refresh();

    expect($shop->name)->toBe('Test Shop Updated');
})->depends('create shop');

test('seed shop permissions from command', function () {
    $this->artisan('shop:seed-permissions')->assertExitCode(0);
})->depends('create shop by command');

test('create website from command', function (Shop $shop) {
    $this->artisan('website:create', [
        'shop'   => $shop->slug,
        'domain' => 'test-hello.com',
        'code'   => 'test',
        'name'   => 'Test Website'
    ])->assertExitCode(0);
    $shop->refresh();
    expect($shop->website)->toBeInstanceOf(Website::class);
})->depends('create shop');
