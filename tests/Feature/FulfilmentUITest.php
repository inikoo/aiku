<?php
/*
 * Author: Arya Permana <aryapermana02@gmail.com>
 * Created: Thu, 13 Jun 2024 13:27:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\Catalogue\Shop\UpdateShop;
use App\Actions\Fulfilment\Pallet\StorePallet;
use App\Actions\Fulfilment\PalletDelivery\SendPalletDeliveryNotification;
use App\Actions\Fulfilment\PalletDelivery\StorePalletDelivery;
use App\Actions\Fulfilment\PalletReturn\SendPalletReturnNotification;
use App\Actions\Fulfilment\PalletReturn\StorePalletReturn;
use App\Actions\Fulfilment\Rental\StoreRental;
use App\Actions\Inventory\Location\StoreLocation;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Enums\Fulfilment\Rental\RentalStateEnum;
use App\Enums\Fulfilment\Rental\RentalUnitEnum;
use App\Enums\UI\Fulfilment\FulfilmentAssetsTabsEnum;
use App\Enums\UI\Fulfilment\FulfilmentsTabsEnum;
use App\Enums\UI\Fulfilment\PhysicalGoodsTabsEnum;
use App\Enums\UI\Fulfilment\RentalsTabsEnum;
use App\Enums\UI\Fulfilment\ServicesTabsEnum;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Fulfilment\Rental;
use App\Models\Inventory\Location;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeAll(function () {
    loadDB();
});

beforeEach(function () {

    SendPalletDeliveryNotification::shouldRun()
        ->andReturn();
    SendPalletReturnNotification::shouldRun()
        ->andReturn();

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
        data_set($storeData, 'warehouses', [$this->warehouse->id]);

        $shop = StoreShop::make()->action(
            $this->organisation,
            $storeData
        );
    }
    $this->shop = $shop;

    $this->shop = UpdateShop::make()->action($this->shop, ['state' => ShopStateEnum::OPEN]);

    $this->customer = createCustomer($this->shop);


    $pallet = Pallet::first();
    if (!$pallet) {
        $storeData = Pallet::factory()->definition();
        data_set($storeData, 'state', PalletStateEnum::SUBMITTED);
        data_set($storeData, 'warehouse_id', $this->warehouse->id);



        $pallet = StorePallet::make()->action(
            $this->customer->fulfilmentCustomer,
            $storeData
        );
    }

    $this->pallet = $pallet;

    $palletDelivery = PalletDelivery::first();
    if (!$palletDelivery) {
        data_set($storeData, 'warehouse_id', $this->warehouse->id);
        data_set($storeData, 'state', PalletDeliveryStateEnum::IN_PROCESS);

        $palletDelivery = StorePalletDelivery::make()->action(
            $this->customer->fulfilmentCustomer,
            $storeData
        );
    }

    $this->palletDelivery = $palletDelivery;

    $palletReturn= PalletReturn::first();
    if (!$palletReturn) {
        data_set($storeData, 'warehouse_id', $this->warehouse->id);
        data_set($storeData, 'state', PalletReturnStateEnum::IN_PROCESS);

        $palletReturn = StorePalletReturn::make()->action(
            $this->customer->fulfilmentCustomer,
            $storeData
        );
    }

    $this->palletReturn = $palletReturn;

    $rental = Rental::first();
    if (!$rental) {
        data_set($storeData, 'code', 'TEST');
        data_set($storeData, 'state', RentalStateEnum::ACTIVE);
        data_set($storeData, 'name', 'testo');
        data_set($storeData, 'price', 100);
        data_set($storeData, 'unit', RentalUnitEnum::DAY->value);

        $rental = StoreRental::make()->action(
            $this->shop,
            $storeData
        );
    }

    $this->rental = $rental;


    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    actingAs($this->adminGuest->user);
});

test('UI list of fulfilment shops', function () {
    $response = get(route('grp.org.fulfilments.index', $this->organisation->slug));
    expect(FulfilmentsTabsEnum::FULFILMENT_SHOPS->value)->toBe('fulfilments');
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/Fulfilments')
            ->has('title')->has('tabs')->has(FulfilmentsTabsEnum::FULFILMENT_SHOPS->value.'.data')
            ->has('breadcrumbs', 2);
    });
});

test('UI create fulfilment', function () {
    $response = get(route('grp.org.fulfilments.create', $this->organisation->slug));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CreateModel')
            ->has('title')->has('formData')->has('pageHead')->has('breadcrumbs', 3);
    });
});

test('UI show fulfilment shop', function () {

    $fulfilment=$this->shop->fulfilment;
    $response  = get(
        route(
            'grp.org.fulfilments.show.operations.dashboard',
            [
                $this->organisation->slug,
                $fulfilment->slug
            ]
        )
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/Fulfilment')
            ->has('title')->has('tabs')
            ->has('breadcrumbs', 2);
    });
});


test('UI show fulfilment shop customers list', function () {

    $fulfilment=$this->shop->fulfilment;

    $response = get(
        route(
            'grp.org.fulfilments.show.crm.customers.index',
            [
                $this->organisation->slug,
                $fulfilment->slug
            ]
        )
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/FulfilmentCustomers')
            ->has('title')
            ->has('breadcrumbs', 3);
    });
});


// Indexes

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

// Fulfilment Customer

test('UI create fulfilment customer', function () {
    $response = get(route('grp.org.fulfilments.show.crm.customers.create', [$this->organisation->slug, $this->fulfilment->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CreateModel')
            ->has('title')->has('formData')->has('pageHead')->has('breadcrumbs', 4);
    });
});

test('UI show fulfilment customer', function () {
    $response = get(route('grp.org.fulfilments.show.crm.customers.show', [$this->organisation->slug, $this->fulfilment->slug, $this->customer->fulfilmentCustomer->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/FulfilmentCustomer')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->customer->name)
                        ->etc()
            )
            ->has('tabs');

    });
});

test('UI show fulfilment customer (agreed prices tab)', function () {
    $response = get('http://app.aiku.test/org/'.$this->organisation->slug.'/fulfilments/'.$this->fulfilment->slug.'/customers/'. $this->customer->fulfilmentCustomer->slug.'?tab=agreed_prices');
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/FulfilmentCustomer')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->customer->name)
                        ->etc()
            )
            ->has('tabs');

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
            ->has(
                'formData.args.updateRoute',
                fn (AssertableInertia $page) => $page
                        ->where('name', 'grp.models.fulfilment-customer.update')
                        ->where('parameters', [$this->customer->fulfilmentCustomer->id])
            )
            ->has('breadcrumbs', 3);
    });
});

// Pallets

test('UI Index pallets', function () {
    $response = $this->get(route('grp.org.fulfilments.show.operations.pallets.index', [$this->organisation->slug, $this->fulfilment->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/Pallets')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'Returned Pallets')
                        ->has('subNavigation')
                        ->has('actions')
                        ->has('meta')
                        ->etc()
            )
            ->has('data');
    });
});

test('UI show pallet', function () {
    $response = get(route('grp.org.fulfilments.show.operations.pallets.show', [$this->organisation->slug, $this->fulfilment->slug, $this->pallet->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/Pallet')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->pallet->reference)
                        ->etc()
            )
            ->has('tabs');

    });
});

test('UI edit pallet', function () {
    $response = get(route('grp.org.fulfilments.show.operations.pallets.edit', [$this->organisation->slug, $this->fulfilment->slug, $this->pallet->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->has('title')
            ->has('formData.blueprint.0.fields', 3)
            ->has('pageHead')
            ->has(
                'formData.args.updateRoute',
                fn (AssertableInertia $page) => $page
                        ->where('name', 'grp.models.pallet.update')
                        ->where('parameters', [$this->pallet->id])
            )
            ->has('breadcrumbs', 3);
    });
});

test('UI Index damaged pallets in warehouse', function () {
    $response = $this->get(route('grp.org.warehouses.show.fulfilment.damaged_pallets.index', [$this->organisation->slug, $this->warehouse->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/Pallets')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'Damaged pallets')
                        ->has('subNavigation')
                        ->etc()
            )
            ->has('data');
    });
});

test('UI Index returned pallets in warehouse', function () {
    $response = $this->get(route('grp.org.warehouses.show.fulfilment.returned_pallets.index', [$this->organisation->slug, $this->warehouse->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/Pallets')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'Returned pallets')
                        ->has('subNavigation')
                        ->etc()
            )
            ->has('data');
    });
});

test('UI Index pallets in warehouse', function () {
    $response = $this->get(route('grp.org.warehouses.show.fulfilment.pallets.index', [$this->organisation->slug, $this->warehouse->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Warehouse/Fulfilment/Pallets')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'Pallets in warehouse')
                        ->has('subNavigation')
                        ->etc()
            )
            ->has('data');
    });
});

test('UI Index lost pallets in warehouse', function () {
    $response = $this->get(route('grp.org.warehouses.show.fulfilment.lost_pallets.index', [$this->organisation->slug, $this->warehouse->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/Pallets')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'Lost pallets')
                        ->has('subNavigation')
                        ->etc()
            )
            ->has('data');
    });
});

// Pallet Delivery

test('UI Index pallet deliveries', function () {
    $response = $this->get(route('grp.org.fulfilments.show.operations.pallet-deliveries.index', [$this->organisation->slug, $this->fulfilment->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/PalletDeliveries')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'deliveries')
                        ->has('subNavigation')
                        ->has('actions')
                        ->etc()
            )
            ->has('data');
    });
});

test('UI show pallet delivery', function () {
    // $this->withoutExceptionHandling();
    $response = get(route('grp.org.fulfilments.show.operations.pallet-deliveries.show', [$this->organisation->slug, $this->fulfilment->slug, $this->palletDelivery->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/PalletDelivery')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->palletDelivery->reference)
                        ->etc()
            )
            ->has('tabs');

    });
});

test('UI show pallet delivery (Services Tab)', function () {
    $response = get('http://app.aiku.test/org/'.$this->organisation->slug.'/fulfilments/'.$this->fulfilment->slug.'/deliveries/'.$this->palletDelivery->slug.'?tab=services');
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/PalletDelivery')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->palletDelivery->reference)
                        ->etc()
            )
            ->has('tabs');

    });
});

test('UI show pallet delivery (Physical goods Tab)', function () {
    $response = get('http://app.aiku.test/org/'.$this->organisation->slug.'/fulfilments/'.$this->fulfilment->slug.'/deliveries/'.$this->palletDelivery->slug.'?tab=physical_goods');
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/PalletDelivery')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->palletDelivery->reference)
                        ->etc()
            )
            ->has('tabs');

    });
});

// Pallet Return

test('UI Index pallet returns', function () {
    $response = $this->get(route('grp.org.fulfilments.show.operations.pallet-returns.index', [$this->organisation->slug, $this->fulfilment->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/PalletReturns')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'returns')
                        ->has('subNavigation')
                        ->has('actions')
                        ->etc()
            )
            ->has('data');
    });
});

test('UI show pallet return', function () {
    // $this->withoutExceptionHandling();
    $response = get(route('grp.org.fulfilments.show.operations.pallet-returns.show', [$this->organisation->slug, $this->fulfilment->slug, $this->palletReturn->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/PalletReturn')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->palletReturn->reference)
                        ->etc()
            )
            ->has('tabs');

    });
});

test('UI show pallet return (physical goods tab)', function () {
    $response = get('http://app.aiku.test/org/'.$this->organisation->slug.'/fulfilments/'.$this->fulfilment->slug.'/returns/'.$this->palletReturn->slug.'?tab=physical_goods');
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/PalletReturn')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->palletReturn->reference)
                        ->etc()
            )
            ->has('tabs');

    });
});

test('UI show pallet return (services tab)', function () {
    $response = get('http://app.aiku.test/org/'.$this->organisation->slug.'/fulfilments/'.$this->fulfilment->slug.'/returns/'.$this->palletReturn->slug.'?tab=services');
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/PalletReturn')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->palletReturn->reference)
                        ->etc()
            )
            ->has('tabs');

    });
});

// Rental

test('UI show rental', function () {

    $response = get(route('grp.org.fulfilments.show.assets.rentals.show', [$this->organisation->slug, $this->fulfilment->slug, $this->rental->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/Rental')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->rental->code)
                        ->etc()
            )
            ->has('tabs');

    });
});

test('UI edit rental', function () {
    $response = get(route('grp.org.fulfilments.show.assets.rentals.edit', [$this->organisation->slug, $this->fulfilment->slug, $this->rental->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->has('title')
            ->has('formData.blueprint.0.fields', 6)
            ->has('pageHead')
            ->has(
                'formData.args.updateRoute',
                fn (AssertableInertia $page) => $page
                        ->where('name', 'grp.models.product.update')
                        ->where('parameters', $this->rental->id)
            )
            ->has('breadcrumbs', 4);
    });
});

test('UI create rental', function () {
    $response = get(route('grp.org.fulfilments.show.assets.rentals.create', [$this->organisation->slug, $this->fulfilment->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CreateModel')
            ->has('title')->has('formData')->has('pageHead')->has('breadcrumbs', 5);
    });
});

// Rental Agreement

test('UI create rental agreement', function () {
    $response = get(route('grp.org.fulfilments.show.crm.customers.show.rental-agreement.create', [$this->organisation->slug, $this->fulfilment->slug, $this->customer->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CreateModel')
            ->has('title')->has('formData')->has('pageHead')->has('breadcrumbs', 4);
    });
});
