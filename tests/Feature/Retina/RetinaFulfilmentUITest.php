<?php

/*
 * Author: Arya Permana <aryapermana02@gmail.com>
 * Created: Wed, 19 Jun 2024 09:24:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

/** @noinspection PhpUnhandledExceptionInspection */

use App\Actions\Fulfilment\PalletDelivery\ConfirmPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\StorePalletDelivery;
use App\Actions\Fulfilment\PalletReturn\StorePalletReturn;
use App\Actions\Fulfilment\RecurringBill\StoreRecurringBill;
use App\Actions\Fulfilment\RentalAgreement\StoreRentalAgreement;
use App\Actions\Retina\Fulfilment\Pallet\StoreRetinaPalletFromDelivery;
use App\Actions\Web\Website\LaunchWebsite;
use App\Actions\Web\Website\UI\DetectWebsiteFromDomain;
use App\Enums\CRM\Customer\CustomerStatusEnum;
use App\Enums\Fulfilment\Pallet\PalletTypeEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementBillingCycleEnum;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementStateEnum;
use App\Enums\Web\Website\WebsiteStateEnum;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Fulfilment\RecurringBill;
use App\Models\Fulfilment\RentalAgreement;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;

uses()->group('ui');

beforeAll(function () {
    loadDB();
});
beforeEach(function () {

    $this->organisation      = createOrganisation();
    $this->warehouse         = createWarehouse();
    $this->fulfilment        = createFulfilment($this->organisation);
    $this->website           = createWebsite($this->fulfilment->shop);
    if ($this->website->state != WebsiteStateEnum::LIVE) {
        LaunchWebsite::make()->action($this->website);
    }
    $this->customer = createCustomer($this->fulfilment->shop);
    $this->customer->update(['status' => CustomerStatusEnum::APPROVED]);

    $rentalAgreement = RentalAgreement::where('fulfilment_customer_id', $this->customer->fulfilmentCustomer->id)->first();
    if (!$rentalAgreement) {
        data_set($storeData, 'billing_cycle', RentalAgreementBillingCycleEnum::MONTHLY);
        data_set($storeData, 'state', RentalAgreementStateEnum::ACTIVE);
        data_set($storeData, 'username', 'test');
        data_set($storeData, 'email', 'test@aiku.io');



        $rentalAgreement = StoreRentalAgreement::make()->action(
            $this->customer->fulfilmentCustomer,
            $storeData,
        );
    }
    $this->rentalAgreement = $rentalAgreement;

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

    $pallet = Pallet::first();
    if (!$pallet) {
        data_set($storeData, 'type', PalletTypeEnum::PALLET);
        data_set($storeData, 'customer_reference', 'ref');
        data_set($storeData, 'notes', 'ref notes');

        $pallet = StoreRetinaPalletFromDelivery::make()->action(
            $this->palletDelivery,
            $storeData
        );
    }

    $this->pallet = $pallet;

    ConfirmPalletDelivery::make()->action($this->palletDelivery);

    $this->pallet->refresh();

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

    $this->webUser  = createWebUser($this->customer);

    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Retina')]
    );
    DetectWebsiteFromDomain::shouldRun()->with('localhost')->andReturn($this->website);

});

test('show log in', function () {
    $response = $this->get(route('retina.login.show'));
    $response->assertInertia(function (AssertableInertia $page) {
        $page->component('Auth/RetinaLogin');
    });
});

test('show redirect if not logged in', function () {
    $response = $this->get(route('retina.dashboard.show'));
    $response->assertRedirect('app/login');
});

test('show dashboard', function () {
    actingAs($this->webUser, 'retina');
    $response = $this->get(route('retina.dashboard.show'));
    $response->assertInertia(function (AssertableInertia $page) {
        $page->component('Dashboard/RetinaDashboard');
    });
});

test('show storage dashboard', function () {
    actingAs($this->webUser, 'retina');
    $response = $this->get(route('retina.fulfilment.storage.dashboard'));
    $response->assertInertia(function (AssertableInertia $page) {
        $page->component('Storage/RetinaStorageDashboard');
    });
});

test('show profile', function () {
    actingAs($this->webUser, 'retina');
    $response = $this->get(route('retina.profile.show'));
    $response->assertInertia(function (AssertableInertia $page) {
        $page->component('EditModel')
        ->has(
            'formData.args.updateRoute',
            fn (AssertableInertia $page) => $page
                    ->where('name', 'retina.models.profile.update')
        );
    });
});

test('create rental agreement', function () {

    $rentalAgreement = StoreRentalAgreement::make()->action(
        $this->webUser->customer->fulfilmentCustomer,
        [
             'billing_cycle' => RentalAgreementBillingCycleEnum::MONTHLY,
             'state'         => RentalAgreementStateEnum::ACTIVE,
             'username'      => 'test',
             'email'         => 'hello@aiku.io',
         ]
    );

    expect($rentalAgreement)->toBeInstanceOf(RentalAgreement::class);


});

test('index pallets', function () {
    actingAs($this->webUser, 'retina');
    $this->withoutExceptionHandling();
    $response = $this->get(route('retina.fulfilment.storage.pallets.index'));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Storage/RetinaPallets')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('pageHead')
            ->has('data');
    });
});

test('show pallet', function () {
    $this->withoutExceptionHandling();
    actingAs($this->webUser, 'retina');
    $response = $this->get(route('retina.fulfilment.storage.pallets.show', [$this->pallet->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Storage/RetinaPallet')
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

test('edit pallet', function () {
    $this->withoutExceptionHandling();
    actingAs($this->webUser, 'retina');
    $response = $this->get(route('retina.fulfilment.storage.pallets.edit', [$this->pallet->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->has(
                'formData.args.updateRoute',
                fn (AssertableInertia $page) => $page
                        ->where('name', 'retina.models.pallet.update')
                        ->etc()
            );

    });
});

test('index pallet deliveries', function () {
    actingAs($this->webUser, 'retina');
    $this->withoutExceptionHandling();
    $response = $this->get(route('retina.fulfilment.storage.pallet_deliveries.index'));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Storage/RetinaPalletDeliveries')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('pageHead')
            ->has('data');
    });
});

test('show pallet delivery (pallet tab)', function () {
    // $this->withoutExceptionHandling();
    actingAs($this->webUser, 'retina');
    $response = $this->get(route('retina.fulfilment.storage.pallet_deliveries.show', [$this->palletDelivery->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Storage/RetinaPalletDelivery')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->palletDelivery->slug)
                        ->etc()
            )
            ->has('tabs');

    });
});

test('show pallet delivery (services tab)', function () {
    $this->withoutExceptionHandling();
    actingAs($this->webUser, 'retina');
    $response = $this->get('/app/fulfilment/storage/pallet-deliveries/'.$this->palletDelivery->slug.'?tab=services');
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Storage/RetinaPalletDelivery')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->etc()
            )
            ->has('tabs');

    });
});

test('show pallet delivery (physical goods tab)', function () {
    $this->withoutExceptionHandling();
    actingAs($this->webUser, 'retina');
    $response = $this->get('/app/fulfilment/storage/pallet-deliveries/'.$this->palletDelivery->slug.'?tab=physical_goods');
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Storage/RetinaPalletDelivery')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->etc()
            )
            ->has('tabs');

    });
});

test('show sysadmin dashboard', function () {
    actingAs($this->webUser, 'retina');
    $response = $this->get(route('retina.sysadmin.dashboard'));
    $response->assertInertia(function (AssertableInertia $page) {
        $page->component('SysAdmin/RetinaSysAdminDashboard');
    });
});

test('index pallet returns', function () {
    actingAs($this->webUser, 'retina');
    $this->withoutExceptionHandling();
    $response = $this->get(route('retina.fulfilment.storage.pallet_returns.index'));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Storage/RetinaPalletReturns')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('pageHead')
            ->has('data');
    });
});

test('show pallet return (pallet tab)', function () {
    // $this->withoutExceptionHandling();
    actingAs($this->webUser, 'retina');
    $response = $this->get(route('retina.fulfilment.storage.pallet_returns.show', [$this->palletReturn->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Storage/RetinaPalletReturn')
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

test('show pallet return (stored item tab)', function () {
    $this->withoutExceptionHandling();
    actingAs($this->webUser, 'retina');
    $response = $this->get('/app/fulfilment/storage/pallet-returns/'.$this->palletReturn->slug.'?tab=stored_items');
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Storage/RetinaPalletReturn')
            ->has('title')
            ->has('breadcrumbs', 2)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->palletReturn->reference)
                        ->etc()
            )
            ->has('tabs');

    });
})->todo();

test('show pallet return (services tab)', function () {
    // $this->withoutExceptionHandling();
    actingAs($this->webUser, 'retina');
    $response = $this->get('/app/fulfilment/storage/pallet-returns/'.$this->palletReturn->slug.'?tab=services');
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Storage/RetinaPalletReturn')
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

test('show pallet return (physical goods tab)', function () {
    // $this->withoutExceptionHandling();
    actingAs($this->webUser, 'retina');
    $response = $this->get('/app/fulfilment/storage/pallet-returns/'.$this->palletReturn->slug.'?tab=physical_goods');
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Storage/RetinaPalletReturn')
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

test('index web users', function () {
    // $this->withoutExceptionHandling();
    actingAs($this->webUser, 'retina');
    $response = $this->get(route('retina.sysadmin.web-users.index'));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('SysAdmin/RetinaWebUsers')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'Users')
                        ->etc()
            )
            ->has('labels')
            ->has('data');

    });
});

test('create web user', function () {
    // $this->withoutExceptionHandling();
    actingAs($this->webUser, 'retina');
    $response = $this->get(route('retina.sysadmin.web-users.create'));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CreateModel')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'Create User')
                        ->etc()
            )
            ->has('formData');

    });
});

test('edit sysadmin settings', function () {
    // $this->withoutExceptionHandling();
    actingAs($this->webUser, 'retina');
    $response = $this->get(route('retina.sysadmin.settings.edit'));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->has('title')
            ->has('breadcrumbs', 2)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'Account management')
                        ->etc()
            )
            ->has('formData');

    });
});

test('index stored items', function () {
    // $this->withoutExceptionHandling();
    actingAs($this->webUser, 'retina');
    $response = $this->get(route('retina.fulfilment.itemised_storage.stored_items.index'));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Storage/RetinaStoredItems')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'SKUs')
                        ->etc()
            )
            ->has('data');

    });
});

test('index stored item audits', function () {
    // $this->withoutExceptionHandling();
    actingAs($this->webUser, 'retina');
    $response = $this->get(route('retina.fulfilment.itemised_storage.stored_items_audits.index'));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Storage/RetinaStoredItemsAudits')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'stored item audits')
                        ->etc()
            )
            ->has('data');

    });
});

test('show dropshipping dashboard', function () {
    actingAs($this->webUser, 'retina');
    $response = $this->get(route('retina.dropshipping.platform.dashboard'));
    $response->assertInertia(function (AssertableInertia $page) {
        $page->component('Dropshipping/DropshippingDashboard');
    });
});

test('index pricing', function () {
    // $this->withoutExceptionHandling();
    actingAs($this->webUser, 'retina');
    $response = $this->get(route('retina.fulfilment.pricing.index'));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Storage/RetinaStoragePricing')
            ->has('title')
            ->has('breadcrumbs', 2)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'Prices')
                        ->etc()
            )
            ->has('currency')
            ->has('assets');

    });
});

test('index pricing (goods)', function () {
    // $this->withoutExceptionHandling();
    actingAs($this->webUser, 'retina');
    $response = $this->get(route('retina.fulfilment.pricing.goods'));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Pricing/RetinaGoods')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'goods')
                        ->etc()
            )
            ->has('data');

    });
});

test('index pricing (services)', function () {
    // $this->withoutExceptionHandling();
    actingAs($this->webUser, 'retina');
    $response = $this->get(route('retina.fulfilment.pricing.services'));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Pricing/RetinaServices')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'services')
                        ->etc()
            )
            ->has('data');

    });
});

test('index pricing (rentals)', function () {
    // $this->withoutExceptionHandling();
    actingAs($this->webUser, 'retina');
    $response = $this->get(route('retina.fulfilment.pricing.rentals'));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Pricing/RetinaRentals')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'rentals')
                        ->etc()
            )
            ->has('data');

    });
});

test('index spaces', function () {
    // $this->withoutExceptionHandling();
    actingAs($this->webUser, 'retina');
    $response = $this->get(route('retina.fulfilment.spaces.index'));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Space/RetinaSpaces')
            ->has('title')
            ->has('breadcrumbs', 2)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'Spaces')
                        ->etc()
            )
            ->has('data');

    });
});

test('show billing dashboard', function () {
    actingAs($this->webUser, 'retina');
    $response = $this->get(route('retina.fulfilment.billing.dashboard'));
    $response->assertInertia(function (AssertableInertia $page) {
        $page->component('Billing/RetinaBillingDashboard');
    });
});

test('index invoices', function () {
    // $this->withoutExceptionHandling();
    actingAs($this->webUser, 'retina');
    $response = $this->get(route('retina.fulfilment.billing.invoices.index'));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Billing/RetinaInvoices')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'Invoices')
                        ->etc()
            )
            ->has('data');

    });
});

test('show next bill', function () {
    // $this->withoutExceptionHandling();
    actingAs($this->webUser, 'retina');
    $response = $this->get(route('retina.fulfilment.billing.next_recurring_bill'));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Billing/RetinaRecurringBill')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->recurringBill->slug)
                        ->etc()
            )
            ->has('currency')
            ->has('box_stats')
            ->has('tabs');

    });
});
