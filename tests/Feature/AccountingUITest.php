<?php
/*
 * Author: Arya Permana <aryapermana02@gmail.com>
 * Created: Thu, 13 Jun 2024 13:27:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Accounting\OrgPaymentServiceProvider\StoreOrgPaymentServiceProvider;
use App\Actions\Accounting\PaymentAccount\StorePaymentAccount;
use App\Actions\Accounting\PaymentServiceProvider\StorePaymentServiceProvider;
use App\Enums\Accounting\PaymentAccount\PaymentAccountTypeEnum;
use App\Enums\Accounting\PaymentServiceProvider\PaymentServiceProviderEnum;
use App\Models\Accounting\OrgPaymentServiceProvider;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeAll(function () {
    loadDB();
});

beforeEach(function () {

    $this->organisation = createOrganisation();
    $this->group        = $this->organisation->group;
    $this->adminGuest   = createAdminGuest($this->organisation->group);

    

    $paymentServiceProvider = PaymentServiceProvider::first();
    if (!$paymentServiceProvider) {
        data_set($storeData, 'type', PaymentServiceProviderEnum::ACCOUNTS);
        data_set($storeData, 'name', 'test');
        data_set($storeData, 'code', 'testcode');

        $paymentServiceProvider = StorePaymentServiceProvider::make()->action(
            $this->paymentServiceProvider,
            $storeData
        );
    }
    $this->paymentServiceProvider = $paymentServiceProvider;

    $orgPaymentServiceProvider = OrgPaymentServiceProvider::first();
    if (!$orgPaymentServiceProvider) {
        data_set($storeData, 'code', 'testcode');

        $orgPaymentServiceProvider = StoreOrgPaymentServiceProvider::make()->action(
            $this->paymentServiceProvider,
            $this->organisation,
            $storeData
        );
    }
    $this->orgPaymentServiceProvider = $orgPaymentServiceProvider;

    $paymentAccount = PaymentAccount::first();
    if (!$paymentAccount) {
        data_set($storeData, 'type', PaymentAccountTypeEnum::ACCOUNT);
        data_set($storeData, 'name', 'testname');
        data_set($storeData, 'code', 'testcode');


        $paymentAccount = StorePaymentAccount::make()->action(
            $this->orgPaymentServiceProvider,
            $storeData
        );
    }
    $this->paymentAccount = $paymentAccount;


    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    actingAs($this->adminGuest->user);
});

test('UI show accounting dashboard', function () {
    $response = get(route('grp.org.accounting.dashboard', $this->organisation->slug));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Accounting/AccountingDashboard')
            ->has('title')
            ->has('breadcrumbs', 2)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'accounting')
                        ->etc()
            )
            ->has('flatTreeMaps');
    });
});

test('UI show list payment service providers', function () {
    $response = get(route('grp.org.accounting.org-payment-service-providers.index', $this->organisation->slug));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Accounting/SelectPaymentServiceProviders')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'Payment Service Providers')
                        ->etc()
            )
            ->has('data')
            ->has('paymentAccountTypes');
    });
});

test('UI show organisation payment service provider', function () {
    $response = get(route('grp.org.accounting.org-payment-service-providers.show', [$this->organisation->slug, $this->orgPaymentServiceProvider->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Accounting/OrgPaymentServiceProvider')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->orgPaymentServiceProvider->slug)
                        ->etc()
            )
            ->has('navigation')
            ->has('tabs');
    });
});

test('UI show organisation payment service provider (stats tab)', function () {
    $response = get('http://app.aiku.test/org/'.$this->organisation->slug.'/accounting/providers/'.$this->orgPaymentServiceProvider->slug.'?tab=stats');

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Accounting/OrgPaymentServiceProvider')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->orgPaymentServiceProvider->slug)
                        ->etc()
            )
            ->has('navigation')
            ->has('tabs');
    });
});

test('UI show organisation payment service provider (payment accounts tab)', function () {
    $response = get('http://app.aiku.test/org/'.$this->organisation->slug.'/accounting/providers/'.$this->orgPaymentServiceProvider->slug.'?tab=payment_accounts');

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Accounting/OrgPaymentServiceProvider')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->orgPaymentServiceProvider->slug)
                        ->etc()
            )
            ->has('navigation')
            ->has('tabs');
    });
});

test('UI show organisation payment service provider (payments tab)', function () {
    $response = get('http://app.aiku.test/org/'.$this->organisation->slug.'/accounting/providers/'.$this->orgPaymentServiceProvider->slug.'?tab=payments');

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Accounting/OrgPaymentServiceProvider')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->orgPaymentServiceProvider->slug)
                        ->etc()
            )
            ->has('navigation')
            ->has('tabs');
    });
});

test('UI show organisation payment service provider (history tab)', function () {
    $response = get('http://app.aiku.test/org/'.$this->organisation->slug.'/accounting/providers/'.$this->orgPaymentServiceProvider->slug.'?tab=history');

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Accounting/OrgPaymentServiceProvider')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->orgPaymentServiceProvider->slug)
                        ->etc()
            )
            ->has('navigation')
            ->has('tabs');
    });
});

test('UI show list payment accounts in organisation payment service provider', function () {
    $response = get(route('grp.org.accounting.org-payment-service-providers.show.payment-accounts.index', [$this->organisation->slug, $this->orgPaymentServiceProvider->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Accounting/PaymentAccounts')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'Payment Accounts')
                        ->etc()
            )
            ->has('data')
            ->has('shops_list');
    });
});

test('UI show payment account in organisation payment service provider', function () {
    $response = get(route('grp.org.accounting.org-payment-service-providers.show.payment-accounts.show', [$this->organisation->slug, $this->orgPaymentServiceProvider->slug, $this->paymentAccount->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Accounting/PaymentAccount')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->paymentAccount->slug)
                        ->has('actions')
                        ->etc()
            )
            ->has('tabs');
    });
});

test('UI show payment account in organisation payment service provider (stats tab)', function () {
    $response = get('http://app.aiku.test/org/'.$this->organisation->slug.'/accounting/providers/'.$this->orgPaymentServiceProvider->slug.'/accounts/'.$this->paymentAccount->slug.'?tab=stats');

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Accounting/PaymentAccount')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->paymentAccount->slug)
                        ->has('actions')
                        ->etc()
            )
            ->has('tabs');
    });
});

test('UI show payment account in organisation payment service provider (payments tab)', function () {
    $response = get('http://app.aiku.test/org/'.$this->organisation->slug.'/accounting/providers/'.$this->orgPaymentServiceProvider->slug.'/accounts/'.$this->paymentAccount->slug.'?tab=payments');

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Accounting/PaymentAccount')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->paymentAccount->slug)
                        ->has('actions')
                        ->etc()
            )
            ->has('tabs');
    });
});

test('UI show payment account in organisation payment service provider (history tab)', function () {
    $response = get('http://app.aiku.test/org/'.$this->organisation->slug.'/accounting/providers/'.$this->orgPaymentServiceProvider->slug.'/accounts/'.$this->paymentAccount->slug.'?tab=history');

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Accounting/PaymentAccount')
            ->has('title')
            ->has('breadcrumbs', 4)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->paymentAccount->slug)
                        ->has('actions')
                        ->etc()
            )
            ->has('tabs');
    });
});

test('UI show list payment accounts', function () {
    $response = get(route('grp.org.accounting.payment-accounts.index', $this->organisation->slug));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Accounting/PaymentAccounts')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'Payment Accounts')
                        ->etc()
            )
            ->has('data')
            ->has('shops_list');
    });
});

test('UI create payment account', function () {
    $response = get(route('grp.org.accounting.payment-accounts.create', $this->organisation->slug));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CreateModel')
            ->has('title')
            ->has('breadcrumbs', 1)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'new payment account')
                        ->has('actions')
                        ->etc()
            )
            ->has('formData.blueprint.0.fields', 6);
    });
});

test('UI edit payment account', function () {
    $response = get(route('grp.org.accounting.payment-accounts.edit', [$this->organisation->slug, $this->paymentAccount->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->paymentAccount->code)
                        ->etc()
            )
            ->has('formData.blueprint.0.fields', 2);
    });
});

test('UI show list payments', function () {
    $response = get(route('grp.org.accounting.payments.index', $this->organisation->slug));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Accounting/Payments')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'payments')
                        ->etc()
            )
            ->has('data');
    });
});

test('UI show list invoices', function () {
    $response = get(route('grp.org.accounting.invoices.index', $this->organisation->slug));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Accounting/Invoices')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('pageHead')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'invoices')
                        ->has('subNavigation')
                        ->etc()
            )
            ->has('data');
    });
});

