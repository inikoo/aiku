<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 26 Nov 2024 20:58:56 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

/** @noinspection PhpUnhandledExceptionInspection */

use App\Actions\Analytics\GetSectionRoute;
use App\Actions\Billables\Rental\StoreRental;
use App\Actions\Billables\Service\StoreService;
use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\Catalogue\Shop\UpdateShop;
use App\Actions\Fulfilment\Pallet\StorePallet;
use App\Actions\Fulfilment\PalletDelivery\Notifications\SendPalletDeliveryNotification;
use App\Actions\Fulfilment\PalletDelivery\StorePalletDelivery;
use App\Actions\Fulfilment\PalletReturn\Notifications\SendPalletReturnNotification;
use App\Actions\Fulfilment\PalletReturn\StorePalletReturn;
use App\Actions\Fulfilment\RecurringBill\StoreRecurringBill;
use App\Actions\Fulfilment\RentalAgreement\StoreRentalAgreement;
use App\Actions\Fulfilment\StoredItem\StoreStoredItem;
use App\Actions\Inventory\Location\StoreLocation;
use App\Enums\Analytics\AikuSection\AikuSectionEnum;
use App\Enums\Billables\Rental\RentalStateEnum;
use App\Enums\Billables\Rental\RentalUnitEnum;
use App\Enums\Billables\Service\ServiceStateEnum;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementBillingCycleEnum;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementStateEnum;
use App\Enums\UI\Fulfilment\FulfilmentAssetsTabsEnum;
use App\Enums\UI\Fulfilment\FulfilmentsTabsEnum;
use App\Enums\UI\Fulfilment\PhysicalGoodsTabsEnum;
use App\Enums\UI\Fulfilment\RentalsTabsEnum;
use App\Enums\UI\Fulfilment\ServicesTabsEnum;
use App\Models\Analytics\AikuScopedSection;
use App\Models\Billables\Rental;
use App\Models\Billables\Service;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Fulfilment\RecurringBill;
use App\Models\Fulfilment\RentalAgreement;
use App\Models\Fulfilment\StoredItem;
use App\Models\Inventory\Location;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses()->group('ui');


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

    list(
        $this->tradeUnit,
        $this->product
    ) = createProduct($this->shop);

    $this->website = createWebsite($this->shop);

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

    $palletReturn = PalletReturn::first();
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
        data_set($storeData, 'name', 'test');
        data_set($storeData, 'price', 100);
        data_set($storeData, 'unit', RentalUnitEnum::DAY->value);

        $rental = StoreRental::make()->action(
            $this->shop,
            $storeData
        );
    }

    $this->rental = $rental;

    $service = Service::first();
    if (!$service) {
        data_set($storeData, 'code', 'TEST');
        data_set($storeData, 'state', ServiceStateEnum::ACTIVE);
        data_set($storeData, 'name', 'test');
        data_set($storeData, 'price', 100);
        data_set($storeData, 'unit', RentalUnitEnum::DAY->value);

        $service = StoreService::make()->action(
            $this->shop,
            $storeData
        );
    }

    $this->service = $service;

    $storedItem = StoredItem::first();
    if (!$storedItem) {
        data_set($storeData, 'reference', 'stored-item-ref');

        $storedItem = StoreStoredItem::make()->action(
            $this->customer->fulfilmentCustomer,
            $storeData
        );
    }

    $this->storedItem = $storedItem;

    $rentalAgreement = RentalAgreement::where('fulfilment_customer_id', $this->customer->fulfilmentCustomer->id)->first();
    if (!$rentalAgreement) {
        data_set($storeData, 'billing_cycle', RentalAgreementBillingCycleEnum::MONTHLY);
        data_set($storeData, 'state', RentalAgreementStateEnum::ACTIVE);
        data_set($storeData, 'username', 'test');
        data_set($storeData, 'email', 'test@aiku.io');



        $rentalAgreement = StoreRentalAgreement::make()->action(
            $this->customer->fulfilmentCustomer,
            $storeData
        );
    }
    $this->rentalAgreement = $rentalAgreement;

    $recurringBill = RecurringBill::first();
    if (!$recurringBill) {
        data_set($storeData, 'start_date', now());

        $recurringBill = StoreRecurringBill::make()->action(
            $this->rentalAgreement,
            $storeData
        );
    }

    $this->recurringBill = $recurringBill;
    $this->artisan('group:seed_aiku_scoped_sections')->assertExitCode(0);

    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    actingAs($this->adminGuest->getUser());
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

test('UI edit fulfilment', function () {
    $this->withoutExceptionHandling();
    $response = get(route('grp.org.fulfilments.show.settings.edit', [$this->organisation->slug, $this->fulfilment->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->has('title')
            ->has('formData.blueprint.0.fields', 3)
            ->has('pageHead')
            ->has(
                'formData.args.updateRoute',
                fn (AssertableInertia $page) => $page
                        ->where('name', 'grp.models.fulfilment.update')
                        ->where('parameters', [$this->fulfilment->id])
            )
            ->has('breadcrumbs', 3);
    });
});

test('UI show fulfilment shop', function () {

    $fulfilment = $this->shop->fulfilment;
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

    $fulfilment = $this->shop->fulfilment;

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
    $response = $this->get(route('grp.org.fulfilments.show.catalogue.index', [$this->organisation->slug, $this->fulfilment->slug]));

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
    $response = $this->get(route('grp.org.fulfilments.show.catalogue.outers.index', [$this->organisation->slug, $this->fulfilment->slug]));

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
    $response = $this->get(route('grp.org.fulfilments.show.catalogue.rentals.index', [$this->organisation->slug, $this->fulfilment->slug]));

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
    $response = $this->get(route('grp.org.fulfilments.show.catalogue.services.index', [$this->organisation->slug, $this->fulfilment->slug]));

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

test('UI show fulfilment customer web users', function () {
    $response = get(route('grp.org.fulfilments.show.crm.customers.show.web-users.index', [$this->organisation->slug, $this->fulfilment->slug, $this->customer->fulfilmentCustomer->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Shop/CRM/WebUsers')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->customer->name)
                        ->etc()
            )
            ->has('tabs');

    });
});

test('UI show fulfilment customer web users (tab requests)', function () {
    $this->withoutExceptionHandling();
    $response = get(route('grp.org.fulfilments.show.crm.customers.show.web-users.index', [
        $this->organisation->slug, $this->fulfilment->slug, $this->customer->fulfilmentCustomer->slug,
        'tab' => 'requests'
    ]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Shop/CRM/WebUsers')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->customer->name)
                        ->etc()
            )
            ->has('tabs')
            ->has('requests');

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
            ->has('formData.blueprint.0.fields', 6)
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

test('UI index fulfilment invoices all', function () {
    $fulfilment = $this->shop->fulfilment;
    $response  = get(
        route(
            'grp.org.fulfilments.show.operations.invoices.all.index',
            [
                $this->organisation->slug,
                $fulfilment->slug
            ]
        )
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Accounting/Invoices')
            ->has('data')
            ->has('pageHead')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'Invoices')
                        ->has('subNavigation')
                        ->etc()
            );
    });
});

test('UI index fulfilment invoices unpaid', function () {
    $fulfilment = $this->shop->fulfilment;
    $response  = get(
        route(
            'grp.org.fulfilments.show.operations.invoices.unpaid_invoices.index',
            [
                $this->organisation->slug,
                $fulfilment->slug
            ]
        )
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Accounting/Invoices')
            ->has('data')
            ->has('pageHead')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'Invoices')
                        ->has('subNavigation')
                        ->etc()
            );
    });
});

// Pallets

test('UI Index pallets', function () {
    $response = $this->get(route('grp.org.fulfilments.show.operations.pallets.current.index', [$this->organisation->slug, $this->fulfilment->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/Pallets')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'Pallets')
                        ->has('subNavigation')
                        ->etc()
            )
            ->has('data');
    });
});

test('UI show pallet', function () {
    $response = get(route('grp.org.fulfilments.show.operations.pallets.current.show', [$this->organisation->slug, $this->fulfilment->slug, $this->pallet->slug]));
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

test('UI show pallet (Stored Items Tab)', function () {
    $response = get('http://app.aiku.test/org/'.$this->organisation->slug.'/fulfilments/'.$this->fulfilment->slug.'/pallets/'.$this->pallet->slug.'?tab=stored_items');
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
    $response = get(route('grp.org.fulfilments.show.operations.pallets.current.edit', [$this->organisation->slug, $this->fulfilment->slug, $this->pallet->slug]));
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
    $response = $this->get(route('grp.org.warehouses.show.inventory.pallets.damaged.index', [$this->organisation->slug, $this->warehouse->slug]));

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
    $response = $this->get(route('grp.org.warehouses.show.inventory.pallets.returned.index', [$this->organisation->slug, $this->warehouse->slug]));

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
    $response = $this->get(route('grp.org.warehouses.show.inventory.pallets.current.index', [$this->organisation->slug, $this->warehouse->slug]));

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
    $response = $this->get(route('grp.org.warehouses.show.inventory.pallets.lost.index', [$this->organisation->slug, $this->warehouse->slug]));

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
    $this->withoutExceptionHandling();
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
                        ->where('title', 'fulfilment deliveries')
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

    $response = get(route('grp.org.fulfilments.show.catalogue.rentals.show', [$this->organisation->slug, $this->fulfilment->slug, $this->rental->slug]));
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
    $response = get(route('grp.org.fulfilments.show.catalogue.rentals.edit', [$this->organisation->slug, $this->fulfilment->slug, $this->rental->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->has('title')
            ->has('formData.blueprint.0.fields', 6)
            ->has('pageHead')
            ->has(
                'formData.args.updateRoute',
                fn (AssertableInertia $page) => $page
                        ->where('name', 'grp.models.rentals.update')
                        ->where('parameters', [
                                'rental'       => $this->rental->id
                        ])
            )
            ->has('breadcrumbs', 4);
    });
});

test('UI create rental', function () {
    $this->withoutExceptionHandling();
    $response = get(route('grp.org.fulfilments.show.catalogue.rentals.create', [$this->organisation->slug, $this->fulfilment->slug]));
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

test('UI edit rental agreement', function () {
    $this->withoutExceptionHandling();
    $response = get(route('grp.org.fulfilments.show.crm.customers.show.rental-agreement.edit', [$this->organisation->slug, $this->fulfilment->slug, $this->customer->fulfilmentCustomer->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->has('title')
            ->has('formData.blueprint.0.fields', 6)
            ->has('pageHead')
            ->has(
                'formData.args.updateRoute',
                fn (AssertableInertia $page) => $page
                        ->where('name', 'grp.models.rental-agreement.update')
                        ->where('parameters', $this->rentalAgreement->id)
            )
            ->has('breadcrumbs', 4);
    });
})->skip('Known issue with $webUser->email being null');

// Service

test('UI show service', function () {

    $response = get(route('grp.org.fulfilments.show.catalogue.services.show', [$this->organisation->slug, $this->fulfilment->slug, $this->service->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/Service')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->service->code)
                        ->etc()
            )
            ->has('tabs');

    });
});

test('UI edit service', function () {
    $response = get(route('grp.org.fulfilments.show.catalogue.services.edit', [$this->organisation->slug, $this->fulfilment->slug, $this->service->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->has('title')
            ->has('formData.blueprint.0.fields', 8)
            ->has('pageHead')
            ->has(
                'formData.args.updateRoute',
                fn (AssertableInertia $page) => $page
                        ->where('name', 'grp.models.services.update')
                        ->where('parameters', [
                            'service'       => $this->service->id
                    ])
            )
            ->has('breadcrumbs', 4);
    });
});

test('UI create service', function () {
    $response = get(route('grp.org.fulfilments.show.catalogue.services.create', [$this->organisation->slug, $this->fulfilment->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CreateModel')
            ->has('title')->has('formData')->has('pageHead')->has('breadcrumbs', 5);
    });
});

// Physical Goods

test('UI show physical goods', function () {
    $response = get(route('grp.org.fulfilments.show.catalogue.outers.show', [$this->organisation->slug, $this->fulfilment->slug, $this->product->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/PhysicalGood')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->product->code)
                        ->etc()
            )
            ->has('tabs');

    });
});

test('UI edit physical goods', function () {
    $response = get(route('grp.org.fulfilments.show.catalogue.outers.edit', [$this->organisation->slug, $this->fulfilment->slug, $this->product->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->has('title')
            ->has('formData.blueprint.0.fields', 7)
            ->has('pageHead')
            ->has(
                'formData.args.updateRoute',
                fn (AssertableInertia $page) => $page
                        ->where('name', 'grp.models.product.update')
                        ->where('parameters', $this->product->id) //wrong route
            )
            ->has('breadcrumbs', 4);
    });
});

test('UI create physical goods', function () {
    $response = get(route('grp.org.fulfilments.show.catalogue.outers.create', [$this->organisation->slug, $this->fulfilment->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CreateModel')
            ->has('title')->has('formData')->has('pageHead')->has('breadcrumbs', 5);
    });
});


test('UI Index stored items', function () {
    $response = $this->get(route('grp.org.fulfilments.show.crm.customers.show.stored-items.index', [$this->organisation->slug, $this->fulfilment->slug, $this->customer->fulfilmentCustomer->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/StoredItems')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->has('subNavigation')
                        ->etc()
            );
    });
});

test('UI show stored item', function () {
    $response = get(route('grp.org.fulfilments.show.crm.customers.show.stored-items.show', [$this->organisation->slug, $this->fulfilment->slug, $this->customer->fulfilmentCustomer->slug, $this->storedItem->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/StoredItem')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->storedItem->slug)
                        ->etc()
            )
            ->has('tabs');

    });
});

test('UI edit stored item', function () {
    $response = get(route('grp.org.fulfilments.show.crm.customers.show.stored-items.edit', [$this->organisation->slug, $this->fulfilment->slug, $this->customer->fulfilmentCustomer->slug, $this->storedItem->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->has('title')
            ->has('formData.blueprint.0.fields', 2)
            ->has('pageHead')
            ->has(
                'formData.args.updateRoute',
                fn (AssertableInertia $page) => $page
                        ->where('name', 'grp.models.stored-items.update')
                        ->where('parameters', $this->storedItem->id) //wrong route
            )
            ->has('breadcrumbs', 4);
    });
});

test('UI Index Recurring Bills', function () {
    $response = get(route('grp.org.fulfilments.show.operations.recurring_bills.index', [$this->organisation->slug, $this->fulfilment->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/RecurringBills')
            ->has('title')
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'recurring bills')
                        ->has('subNavigation')
                        ->etc()
            );
    });
});

test('UI show Recurring Bill', function () {
    $this->withoutExceptionHandling();
    $response = get(route('grp.org.fulfilments.show.operations.recurring_bills.show', [$this->organisation->slug, $this->fulfilment->slug, $this->recurringBill->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Fulfilment/RecurringBill')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->recurringBill->slug)
                        ->etc()
            )
            ->has('timeline_rb')
            ->has('consolidateRoute')
            ->has('status_rb')
            ->has('box_stats')
            ->has('tabs');

    });
});

test('UI edit recurring bill', function () {
    $this->withoutExceptionHandling();
    $response = get(route('grp.org.fulfilments.show.operations.recurring_bills.edit', [$this->organisation->slug, $this->fulfilment->slug, $this->recurringBill->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->has('title')
            ->has('formData.blueprint.0.fields', 1)
            ->has('pageHead')
            ->has(
                'formData.args.updateRoute',
                fn (AssertableInertia $page) => $page
                        ->where('name', 'grp.models.recurring-bill.update')
                        ->where('parameters.recurringBill', $this->recurringBill->id)
            )
            ->has('breadcrumbs', 4);
    });
});


test('UI get section route fulfilment catalogue index', function () {
    $sectionScope = GetSectionRoute::make()->handle('grp.org.fulfilments.show.catalogue.index', [
        'organisation' => $this->organisation->slug,
        'fulfilment' => $this->fulfilment->slug
    ]);

    expect($sectionScope)->toBeInstanceOf(AikuScopedSection::class)
        ->and($sectionScope->code)->toBe(AikuSectionEnum::FULFILMENT_CATALOGUE->value)
        ->and($sectionScope->model_slug)->toBe($this->fulfilment->slug);
});

test('UI get section route fulfilment operations dashboard', function () {
    $sectionScope = GetSectionRoute::make()->handle('grp.org.fulfilments.show.operations.dashboard', [
        'organisation' => $this->organisation->slug,
        'fulfilment' => $this->fulfilment->slug
    ]);

    expect($sectionScope)->toBeInstanceOf(AikuScopedSection::class)
        ->and($sectionScope->code)->toBe(AikuSectionEnum::FULFILMENT_OPERATION->value)
        ->and($sectionScope->model_slug)->toBe($this->fulfilment->slug);
});

test('UI get section route fulfilment web index', function () {
    $sectionScope = GetSectionRoute::make()->handle('grp.org.fulfilments.show.web.websites.index', [
        'organisation' => $this->organisation->slug,
        'fulfilment' => $this->fulfilment->slug
    ]);

    expect($sectionScope)->toBeInstanceOf(AikuScopedSection::class)
        ->and($sectionScope->code)->toBe(AikuSectionEnum::FULFILMENT_WEBSITE->value)
        ->and($sectionScope->model_slug)->toBe($this->fulfilment->slug);
});

test('UI get section route fulfilment crm index', function () {
    $sectionScope = GetSectionRoute::make()->handle('grp.org.fulfilments.show.crm.customers.index', [
        'organisation' => $this->organisation->slug,
        'fulfilment' => $this->fulfilment->slug
    ]);

    expect($sectionScope)->toBeInstanceOf(AikuScopedSection::class)
        ->and($sectionScope->code)->toBe(AikuSectionEnum::FULFILMENT_CRM->value)
        ->and($sectionScope->model_slug)->toBe($this->fulfilment->slug);
});

test('UI get section route fulfilment settings edit', function () {
    $sectionScope = GetSectionRoute::make()->handle('grp.org.fulfilments.show.settings.edit', [
        'organisation' => $this->organisation->slug,
        'fulfilment' => $this->fulfilment->slug
    ]);

    expect($sectionScope)->toBeInstanceOf(AikuScopedSection::class)
        ->and($sectionScope->code)->toBe(AikuSectionEnum::FULFILMENT_SETTINGS->value)
        ->and($sectionScope->model_slug)->toBe($this->fulfilment->slug);
});

test('UI get section route org fulfilments index', function () {
    $sectionScope = GetSectionRoute::make()->handle('grp.org.fulfilments.index', [
        'organisation' => $this->organisation->slug,
    ]);

    expect($sectionScope)->toBeInstanceOf(AikuScopedSection::class)
        ->and($sectionScope->code)->toBe(AikuSectionEnum::ORG_FULFILMENT->value)
        ->and($sectionScope->model_slug)->toBe($this->organisation->slug);
});
