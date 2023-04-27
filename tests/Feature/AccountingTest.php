<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Apr 2023 16:05:15 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Accounting\Payment\StorePayment;
use App\Actions\Accounting\Payment\UpdatePayment;
use App\Actions\Accounting\PaymentAccount\StorePaymentAccount;
use App\Actions\Accounting\PaymentAccount\UpdatePaymentAccount;
use App\Actions\Accounting\PaymentServiceProvider\StorePaymentServiceProvider;
use App\Actions\Accounting\PaymentServiceProvider\UpdatePaymentServiceProvider;
use App\Enums\Accounting\PaymentServiceProvider\PaymentServiceProviderTypeEnum;
use App\Models\Accounting\Payment;
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

test('can not update payment service provider type', function ($paymentServiceProvider) {
    $paymentServiceProvider = UpdatePaymentServiceProvider::make()->action($paymentServiceProvider, ['type' => PaymentServiceProviderTypeEnum::BANK->value]);
    expect($paymentServiceProvider->type)->not->toBe(PaymentServiceProviderTypeEnum::BANK->value);
})->depends('create payment service provider');

test('update payment service provider code', function ($paymentServiceProvider) {
    $paymentServiceProvider = UpdatePaymentServiceProvider::make()->action($paymentServiceProvider, ['code' => 'hello']);
    expect($paymentServiceProvider->code)->toBe('hello');
})->depends('create payment service provider');

test('create payment account', function ($paymentServiceProvider) {
    $paymentAccount = StorePaymentAccount::make()->action($paymentServiceProvider, PaymentAccount::factory()->definition());
    $this->assertModelExists($paymentAccount);
    return $paymentAccount;
})->depends('create payment service provider');

test('update payment account', function ($paymentAccount) {
    $paymentAccount = UpdatePaymentAccount::make()->action($paymentAccount, ['name' => 'Pika Ltd']);
    expect($paymentAccount->name)->toBe('Pika Ltd');
})->depends('create payment account');
/*
test('create payment', function ($paymentAccount) {
    $payment = StorePayment::make()->action($paymentAccount, Payment::factory()->definition());
    $this->assertModelExists($payment);
    return $payment;
})->depends('create payment account');

test('update payment', function ($payment) {
    $payment = UpdatePayment::make()->action($payment, ['amount' => 1500]);
    expect($payment->amount)->toBe(1500);

})->depends('create payment');
*/
