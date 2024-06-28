<?php
/*
 * Author: Arya Permana <aryapermana02@gmail.com>
 * Created: Wed, 19 Jun 2024 09:24:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\Fulfilment\FulfilmentCustomer\StoreFulfilmentCustomer;
use App\Actions\Fulfilment\PalletDelivery\StorePalletDelivery;
use App\Actions\Fulfilment\RentalAgreement\StoreRentalAgreement;
use App\Actions\Web\Website\LaunchWebsite;
use App\Actions\Web\Website\UI\DetectWebsiteFromDomain;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementBillingCycleEnum;
use App\Enums\Web\Website\WebsiteStateEnum;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
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
    if($this->website->state != WebsiteStateEnum::LIVE) {
        LaunchWebsite::make()->action($this->website);
    }

    $this->customer = createCustomer($this->fulfilment->shop);

    $rentalAgreement = RentalAgreement::where('fulfilment_customer_id', $this->customer->fulfilmentCustomer->id)->first();
    if (!$rentalAgreement) {
        data_set($storeData, 'billing_cycle', RentalAgreementBillingCycleEnum::MONTHLY);
        $rentalAgreement = StoreRentalAgreement::make()->action(
            $this->customer->fulfilmentCustomer,
            $storeData
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
        $page->component('Auth/Login');
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
        $page->component('Dashboard/Dashboard');
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

test('index pallets', function () {
    actingAs($this->webUser, 'retina');
    $response = $this->get(route('retina.storage.pallets.index'));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Storage/RetinaPallets')
            ->has('title')
            ->has('breadcrumbs', 2)
            ->has('pageHead')
            ->has('data');
    });
});

test('index pallet deliveries', function () {
    actingAs($this->webUser, 'retina');
    $this->withoutExceptionHandling();
    $response = $this->get(route('retina.storage.pallet-deliveries.index'));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Storage/RetinaPalletDeliveries')
            ->has('title')
            ->has('breadcrumbs', 2)
            ->has('pageHead')
            ->has('data');
    });
})->todo(); //authorization problem

test('show pallet delivery (pallet tab)', function () {
    // $this->withoutExceptionHandling();
    actingAs($this->webUser, 'retina');
    $response = $this->get(route('retina.storage.pallet-deliveries.show', [$this->palletDelivery->slug]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Storage/RetinaPalletDelivery')
            ->has('title')
            ->has('breadcrumbs', 2)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->palletDelivery->reference)
                        ->etc()
            )
            ->has('tabs');

    });
});