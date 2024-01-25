<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 02 Jan 2024 22:04:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\CRM\Customer\StoreCustomer;
use App\Actions\Market\Shop\StoreShop;
use App\Enums\CRM\Customer\CustomerStatusEnum;
use App\Enums\Market\Shop\ShopTypeEnum;
use App\Enums\UI\FulfilmentTabsEnum;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Permission;
use App\Models\SysAdmin\Role;

use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeAll(function () {
    loadDB('test_base_database.dump');
});


beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->adminGuest   = createAdminGuest($this->organisation->group);

    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    actingAs($this->adminGuest->user);
});

test('create fulfilment shop', function () {
    $organisation = $this->organisation;
    $storeData    = Shop::factory()->definition();
    data_set($storeData, 'type', ShopTypeEnum::FULFILMENT->value);
    $shop = StoreShop::make()->action($this->organisation, $storeData);
    $organisation->refresh();

    $shopRoles             = Role::where('scope_type', 'Shop')->where('scope_id', $shop->id)->get();
    $shopPermissions       = Permission::where('scope_type', 'Shop')->where('scope_id', $shop->id)->get();
    $fulfilmentRoles       = Role::where('scope_type', 'Fulfilment')->where('scope_id', $shop->id)->get();
    $fulfilmentPermissions = Permission::where('scope_type', 'Fulfilment')->where('scope_id', $shop->id)->get();

    expect($shop)->toBeInstanceOf(Shop::class)
        ->and($shop->fulfilment)->toBeInstanceOf(Fulfilment::class)
        ->and($organisation->marketStats->number_shops)->toBe(1)
        ->and($organisation->marketStats->number_shops_type_b2b)->toBe(0)
        ->and($organisation->marketStats->number_shops_type_fulfilment)->toBe(1)
        ->and($shopRoles->count())->toBe(0)
        ->and($shopPermissions->count())->toBe(0)
        ->and($fulfilmentRoles->count())->toBe(3)
        ->and($fulfilmentPermissions->count())->toBe(8);

    $user = $this->adminGuest->user;
    $user->refresh();

    expect($user->getAllPermissions()->count())->toBe(15)
        ->and($user->hasAllRoles(["fulfilment-admin-$shop->fulfilment->id"]))->toBe(false)
        ->and($user->hasAllRoles(["shop-admin-$shop->id"]))->toBe(false);


    return $shop;
});

test('create fulfilment customer', function ($shop) {
    $customer = StoreCustomer::make()->action(
        $shop,
        Customer::factory()->definition(),
    );

    expect($customer)->toBeInstanceOf(Customer::class)
        ->and($customer->reference)->toBe('000001')
        ->and($customer->status)->toBe(CustomerStatusEnum::APPROVED)
        ->and($customer->fulfilment)->toBeInstanceOf(FulfilmentCustomer::class)
        ->and($customer->fulfilment->number_pallets)->toBe(0)
        ->and($customer->fulfilment->number_stored_items)->toBe(0);

    return $customer;
})->depends('create fulfilment shop');

test('can show list of fulfilment shops', function () {
    $response = get(route('grp.org.fulfilment.index', $this->organisation->slug));
    expect(FulfilmentTabsEnum::FULFILMENT_SHOPS->value)->toBe('fulfilments');
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/Fulfilments')
            ->has('title')->has('tabs')->has(FulfilmentTabsEnum::FULFILMENT_SHOPS->value.'.data')
            ->has('breadcrumbs', 2);
    });
});
