<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 02 Jan 2024 22:04:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Market\Shop\StoreShop;
use App\Enums\Market\Shop\ShopTypeEnum;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Permission;
use App\Models\SysAdmin\Role;

beforeAll(function () {
    loadDB('test_base_database.dump');
});


beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->guest        = createAdminGuest($this->organisation->group);
});

test('create fulfilment shop', function () {
    $organisation = $this->organisation;
    $storeData    = Shop::factory()->definition();
    data_set($storeData, 'type', ShopTypeEnum::FULFILMENT->value);
    $shop = StoreShop::make()->action($this->organisation, $storeData);
    $organisation->refresh();

    $shopRoles            =Role::where('scope_type', 'Shop')->where('scope_id', $shop->id)->get();
    $shopPermissions      =Permission::where('scope_type', 'Shop')->where('scope_id', $shop->id)->get();
    $fulfilmentRoles      =Role::where('scope_type', 'Fulfilment')->where('scope_id', $shop->id)->get();
    $fulfilmentPermissions=Permission::where('scope_type', 'Fulfilment')->where('scope_id', $shop->id)->get();

    expect($shop)->toBeInstanceOf(Shop::class)
        ->and($shop->fulfilment)->toBeInstanceOf(Fulfilment::class)
        ->and($organisation->marketStats->number_shops)->toBe(1)
        ->and($organisation->marketStats->number_shops_type_b2b)->toBe(0)
        ->and($organisation->marketStats->number_shops_type_fulfilment)->toBe(1)
        ->and($shopRoles->count())->toBe(0)
        ->and($shopPermissions->count())->toBe(0)
        ->and($fulfilmentRoles->count())->toBe(3)
        ->and($fulfilmentPermissions->count())->toBe(8);

    $user= $this->guest->user;
    $user->refresh();

    expect($user->getAllPermissions()->count())->toBe(15)
        ->and($user->hasAllRoles(["fulfilment-admin-$shop->fulfilment->id"]))->toBe(false)
        ->and($user->hasAllRoles(["shop-admin-$shop->id"]))->toBe(false);


    return $shop;
});
