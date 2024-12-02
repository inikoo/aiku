<?php

/*
 * Author: Arya Permana <aryapermana02@gmail.com>
 * Created: Thu, 13 Jun 2024 13:27:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Accounting\Invoice\StoreInvoice;
use App\Actions\Accounting\OrgPaymentServiceProvider\StoreOrgPaymentServiceProvider;
use App\Actions\Accounting\PaymentAccount\StorePaymentAccount;
use App\Actions\Accounting\PaymentServiceProvider\StorePaymentServiceProvider;
use App\Actions\Analytics\GetSectionRoute;
use App\Enums\Accounting\PaymentAccount\PaymentAccountTypeEnum;
use App\Enums\Accounting\PaymentServiceProvider\PaymentServiceProviderEnum;
use App\Enums\Analytics\AikuSection\AikuSectionEnum;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\OrgPaymentServiceProvider;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Analytics\AikuScopedSection;
use App\Models\CRM\Customer;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeAll(function () {
    loadDB();
});

beforeEach(
    /**
     * @throws \Throwable
     */
    function () {
        list(
            $this->organisation,
            $this->user,
            $this->shop
        ) = createShop();
        $this->group = $this->organisation->group;
        $this->adminGuest = createAdminGuest($this->organisation->group);


        $paymentServiceProvider = PaymentServiceProvider::first();
        if (!$paymentServiceProvider) {
            data_set($storeData, 'type', PaymentServiceProviderEnum::ACCOUNTS);
            data_set($storeData, 'name', 'test');
            data_set($storeData, 'code', 'test-code');

            $paymentServiceProvider = StorePaymentServiceProvider::make()->action(
                $this->paymentServiceProvider,
                $storeData
            );
        }
        $this->paymentServiceProvider = $paymentServiceProvider;

        $orgPaymentServiceProvider = OrgPaymentServiceProvider::first();
        if (!$orgPaymentServiceProvider) {
            data_set($storeData, 'code', 'test-code');

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
            data_set($storeData, 'name', 'test name');
            data_set($storeData, 'code', 'test-code');


            $paymentAccount = StorePaymentAccount::make()->action(
                $this->orgPaymentServiceProvider,
                $storeData
            );
        }
        $this->paymentAccount = $paymentAccount;

        $customer = Customer::first();

        if (!$customer) {
            $customer = createCustomer($this->shop);
        }
        $this->customer = $customer;

        $invoice = Invoice::first();
        if (!$invoice) {
            $invoice = StoreInvoice::make()->action($this->customer, Invoice::factory()->definition());
        }
        $this->invoice = $invoice;
        $this->artisan('group:seed_aiku_scoped_sections')->assertExitCode(0);

        Config::set(
            'inertia.testing.page_paths',
            [resource_path('js/Pages/Grp')]
        );
        actingAs($this->adminGuest->getUser());
    }
);

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
    $this->withoutExceptionHandling();
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
                    ->where('title', 'Invoices')
                    ->has('subNavigation')
                    ->etc()
            )
            ->has('data');
    });
});

test('UI show invoice', function () {
    $response = get(route('grp.org.accounting.invoices.show', [$this->organisation->slug, $this->invoice->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page->component('Org/Accounting/Invoice')
            ->has('title')
            ->has('breadcrumbs')
            ->has(
                'navigation',
                fn (AssertableInertia $page) => $page
                    ->has('previous')
                    ->has('next')
            )
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                    ->where('model', 'invoice')
                    ->where('title', $this->invoice->reference)
                    ->etc()
            )
            ->has(
                'tabs',
                fn (AssertableInertia $page) => $page
                    ->has('current')
                    ->has('navigation')
            )
            ->has('order_summary', 3)
            ->has('exportPdfRoute')
            ->has(
                'box_stats',
                fn (AssertableInertia $page) => $page
                    ->has(
                        'customer',
                        fn (AssertableInertia $page) => $page
                            ->has('slug')
                            ->has('reference')
                            ->has('route')
                            ->has('contact_name')
                            ->has('company_name')
                            ->has('location')
                            ->has('phone')
                    )
                    ->has(
                        'information',
                        fn (AssertableInertia $page) => $page
                            ->has('recurring_bill')
                            ->has('routes')
                            ->has('paid_amount')
                            ->has('pay_amount')
                    )
            )
            ->has('invoice');
    });
});

test('UI index customer balances', function () {
    $response = get(route('grp.org.accounting.balances.index', [$this->organisation->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page->component('Org/Accounting/CustomerBalances')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                    ->where('title', 'Customer Balances')
                    ->etc()
            )
            ->has('data');
    });
});

test('UI get section route accounting balance index', function () {
    $sectionScope = GetSectionRoute::make()->handle('grp.org.accounting.balances.index', [
        'organisation' => $this->organisation->slug,
    ]);

    expect($sectionScope)->toBeInstanceOf(AikuScopedSection::class)
        ->and($sectionScope->code)->toBe(AikuSectionEnum::ORG_ACCOUNTING->value)
        ->and($sectionScope->model_slug)->toBe($this->organisation->slug);
});

test('UI get section route group overview hub (accounting)', function () {
    $sectionScope = GetSectionRoute::make()->handle('grp.overview.hub', []);
    expect($sectionScope)->toBeInstanceOf(AikuScopedSection::class)
        ->and($sectionScope->code)->toBe(AikuSectionEnum::GROUP_OVERVIEW->value)
        ->and($sectionScope->model_slug)->toBe($this->organisation->group->slug);
});
