<?php
/*
 * Author: Arya Permana <aryapermana02@gmail.com>
 * Created: Thu, 13 Jun 2024 13:27:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\Catalogue\Shop\UpdateShop;
use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilmentAssets;
use App\Actions\Fulfilment\FulfilmentCustomer\StoreFulfilmentCustomer;
use App\Actions\Inventory\Location\StoreLocation;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\UI\Fulfilment\FulfilmentAssetsTabsEnum;
use App\Enums\UI\Fulfilment\PhysicalGoodsTabsEnum;
use App\Enums\UI\Fulfilment\RentalsTabsEnum;
use App\Enums\UI\Fulfilment\ServicesTabsEnum;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Inventory\Location;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia;
use Lorisleiva\Actions\ActionRequest;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeAll(function () {
    loadDB('test_base_database.dump');
});

beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->adminGuest   = createAdminGuest($this->organisation->group);
    $this->warehouse    = createWarehouse();
    $this->fulfilment   = createFulfilment($this->organisation);
    $location           = $this->warehouse->locations()->first();
    if (!$location) {
        StoreLocation::run(
            $this->warehouse,
            Location::factory()->definition()
        );
        StoreLocation::run(
            $this->warehouse,
            Location::factory()->definition()
        );
    }

    $shop = Shop::first();
    if (!$shop) {
        $storeData = Shop::factory()->definition();
        data_set($storeData, 'type', ShopTypeEnum::FULFILMENT);
        data_set($storeData,'warehouses',[$this->warehouse->id]);

        $shop = StoreShop::make()->action(
            $this->organisation,
            $storeData
        );
    }
    $this->shop = $shop;

    $this->shop = UpdateShop::make()->action($this->shop, ['state' => ShopStateEnum::OPEN]);

    $this->customer = createCustomer($this->shop);

    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    actingAs($this->adminGuest->user);
});

test('UI Index fulfilment assets', function () {
    $response = $this->get(route('grp.org.fulfilments.show.assets.index', [$this->organisation->slug, $this->fulfilment->slug]));

    expect(FulfilmentAssetsTabsEnum::DASHBOARD->value)->toBe('dashboard');

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/Products')
            ->has('title')
            ->has('tabs')
            ->has('breadcrumbs', 3);
    });  
});

test('UI Index fulfilment physical goods', function () {
    $response = $this->get(route('grp.org.fulfilments.show.assets.outers.index', [$this->organisation->slug, $this->fulfilment->slug]));

    expect(PhysicalGoodsTabsEnum::PHYSICAL_GOODS->value)->toBe('physical_goods');

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/PhysicalGoods')
            ->has('title')
            ->has('tabs')
            ->has('breadcrumbs', 4);
    });  
});

test('UI Index fulfilment rentals', function () {
    $response = $this->get(route('grp.org.fulfilments.show.assets.rentals.index', [$this->organisation->slug, $this->fulfilment->slug]));

    expect(RentalsTabsEnum::RENTALS->value)->toBe('rentals');

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/Rentals')
            ->has('title')
            ->has('tabs')
            ->has('breadcrumbs', 4);
    });  
});

test('UI Index fulfilment services', function () {
    $response = $this->get(route('grp.org.fulfilments.show.assets.services.index', [$this->organisation->slug, $this->fulfilment->slug]));

    expect(ServicesTabsEnum::SERVICES->value)->toBe('services');

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/Services')
            ->has('title')
            ->has('tabs')
            ->has('breadcrumbs', 4);
    });  
});

test('UI create fulfilment customer', function () {
    $response = get(route('grp.org.fulfilments.show.crm.customers.create', [$this->organisation->slug, $this->fulfilment->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CreateModel')
            ->has('title')->has('formData')->has('pageHead')->has('breadcrumbs', 4);
    });
});

test('UI edit fulfilment customer', function () {
    $response = get(route('grp.org.fulfilments.show.crm.customers.show.edit', [$this->organisation->slug, $this->fulfilment->slug, $this->customer->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->has('title')
            ->has('formData.blueprint.0.fields', 4)
            ->has('pageHead')
            ->has('formData.args.updateRoute', fn (AssertableInertia $page) => $page
                        ->where('name', 'grp.models.fulfilment-customer.update')
                        ->where('parameters', [$this->customer->fulfilmentCustomer->id])
                    )
            ->has('breadcrumbs', 3);
    });
});


