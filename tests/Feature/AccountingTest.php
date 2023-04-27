<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Apr 2023 16:05:15 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Accounting\PaymentAccount\StorePaymentAccount;
use App\Actions\Accounting\PaymentAccount\UpdatePaymentAccount;
use App\Actions\Accounting\PaymentServiceProvider\StorePaymentServiceProvider;
use App\Actions\Accounting\PaymentServiceProvider\UpdatePaymentServiceProvider;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Tenancy\Tenant;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();

});

test('create payment service provider', function () {
    $paymentServiceProvider = StorePaymentServiceProvider::make()->action(PaymentServiceProvider::factory()->definition());
    $this->assertModelExists($paymentServiceProvider);
    return $paymentServiceProvider;
});

test('update payment service provider', function ($paymentServiceProvider) {
    $paymentServiceProvider = UpdatePaymentServiceProvider::make()->action($paymentServiceProvider, ['name' => 'Pika Ltd']);
    expect($paymentServiceProvider->payments)->toBe('Pika Ltd');
})->depends('create create payment service provider');

test('create payment account', function ($paymentServiceProvider) {
    // $warehouse = Warehouse::factory()->create();
    $paymentAccount = StorePaymentAccount::make()->action($paymentServiceProvider, PaymentAccount::factory()->definition());
    $this->assertModelExists($paymentAccount);
    return $paymentAccount;
})->depends('create payment service provider');

test('update warehouse area', function ($paymentAccount) {
    $paymentAccount = UpdatePaymentAccount::make()->action($paymentAccount, ['name' => 'Pika Ltd']);
    expect($paymentAccount->name)->toBe('Pika Ltd');
})->depends('create payment account');
