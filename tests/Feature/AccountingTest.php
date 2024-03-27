<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 27 Mar 2024 21:34:44 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Accounting\Payment\StorePayment;
use App\Actions\Accounting\PaymentAccount\StorePaymentAccount;
use App\Actions\Accounting\PaymentAccount\UpdatePaymentAccount;
use App\Actions\Accounting\PaymentServiceProvider\StorePaymentServiceProvider;
use App\Actions\Accounting\PaymentServiceProvider\UpdatePaymentServiceProvider;
use App\Actions\CRM\Customer\StoreCustomer;
use App\Actions\Helpers\CurrencyExchange\GetCurrencyExchange;
use App\Actions\Market\Shop\StoreShop;
use App\Enums\Accounting\PaymentAccount\PaymentAccountTypeEnum;
use App\Enums\Accounting\PaymentServiceProvider\PaymentServiceProviderTypeEnum;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\CRM\Customer;
use App\Models\Market\Shop;
use Illuminate\Validation\ValidationException;

beforeAll(function () {
    loadDB('test_base_database.dump');
});

beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->group        = $this->organisation->group;
});


test('create payment service provider', function () {
    expect($this->organisation->accountingStats->number_payment_service_providers)->toBe(1)
        ->and($this->organisation->accountingStats->number_payment_service_providers_type_account)->toBe(1);

    $modelData = PaymentServiceProvider::factory()->definition();
    data_set($modelData, 'type', PaymentServiceProviderTypeEnum::CASH->value);
    $paymentServiceProvider = StorePaymentServiceProvider::make()->action(
        organisation: $this->organisation,
        modelData: $modelData
    );
    $this->organisation->refresh();
    expect($paymentServiceProvider)->toBeInstanceOf(PaymentServiceProvider::class)
        ->and($this->group->accountingStats->number_payment_service_providers)->toBe(2)
        ->and($this->group->accountingStats->number_payment_service_providers_type_account)->toBe(1)
        ->and($this->group->accountingStats->number_payment_service_providers_type_cash)->toBe(1)
        ->and($this->organisation->accountingStats->number_payment_service_providers)->toBe(2)
        ->and($this->organisation->accountingStats->number_payment_service_providers_type_cash)->toBe(1);

    return $paymentServiceProvider;
});

test('can not update payment service provider type', function ($paymentServiceProvider) {
    $paymentServiceProvider = UpdatePaymentServiceProvider::make()->action($paymentServiceProvider, ['type' => PaymentServiceProviderTypeEnum::BANK->value]);
    expect($paymentServiceProvider->type)->not->toBe(PaymentServiceProviderTypeEnum::BANK->value);
})->depends('create payment service provider');

test('update payment service provider code and name', function ($paymentServiceProvider) {
    $paymentServiceProvider = UpdatePaymentServiceProvider::make()->action(
        $paymentServiceProvider,
        ['code' => 'hello', 'name' => 'new name']
    );
    expect($paymentServiceProvider->code)->toBe('hello')->and($paymentServiceProvider->name)->toBe('new name');
})->depends('create payment service provider');

test('can not create payment service same code ', function () {
    $modelData = PaymentServiceProvider::factory()->definition();
    data_set($modelData, 'type', PaymentServiceProviderTypeEnum::BANK->value);
    data_set($modelData, 'code', 'hello');
    StorePaymentServiceProvider::make()->action(
        organisation: $this->organisation,
        modelData: $modelData
    );
})->expectException(ValidationException::class);

test('create other payment service provider', function () {
    $modelData = PaymentServiceProvider::factory()->definition();
    data_set($modelData, 'code', 'hello2');
    $paymentServiceProvider = StorePaymentServiceProvider::make()->action(
        organisation: $this->organisation,
        modelData: $modelData
    );
    $this->organisation->refresh();
    expect($paymentServiceProvider)->toBeInstanceOf(PaymentServiceProvider::class)
        ->and($this->group->accountingStats->number_payment_service_providers)->toBe(3)
        ->and($this->organisation->accountingStats->number_payment_service_providers)->toBe(3);

    return $paymentServiceProvider;
});

test('can not update payment service duplicated code', function ($paymentServiceProvider) {
    UpdatePaymentServiceProvider::make()->action(
        $paymentServiceProvider,
        ['code' => 'hello']
    );
})->depends('create other payment service provider')->expectException(ValidationException::class);

//todo restrict payments account types depending of the Service Account type
test('create payment account', function ($paymentServiceProvider) {

    $modelData = PaymentAccount::factory()->definition();
    data_set($modelData, 'type', PaymentAccountTypeEnum::BANK->value);

    $paymentAccount = StorePaymentAccount::make()->action(
        $paymentServiceProvider,
        $modelData
    );
    $paymentServiceProvider->refresh();
    expect($paymentAccount)->toBeInstanceOf(PaymentAccount::class)
        ->and($paymentServiceProvider->stats->number_payment_accounts)->toBe(1)
        ->and($paymentServiceProvider->stats->number_payment_accounts_type_bank)->toBe(1);

    return $paymentAccount;
})->depends('create payment service provider');

test('update payment account', function ($paymentAccount) {
    $paymentAccount = UpdatePaymentAccount::make()->action($paymentAccount, ['name' => 'Pika Ltd']);
    expect($paymentAccount->name)->toBe('Pika Ltd');
})->depends('create payment account');


test(
    'create payment',
    function ($paymentAccount) {
        GetCurrencyExchange::shouldRun()
            ->andReturn(2);

        $shop     = StoreShop::make()->action($this->organisation, Shop::factory()->definition());
        $customer = StoreCustomer::make()->action(
            $shop,
            Customer::factory()->definition()
        );

        $modelData=Payment::factory()->definition();
        $payment  = StorePayment::make()->action($customer, $paymentAccount, $modelData);

        $this->organisation->refresh();

        expect($payment)->toBeInstanceOf(Payment::class)
            ->and($payment->shop_id)->toBe($shop->id)
            ->and($payment->currency_id)->toBe($shop->currency_id)
            ->and($payment->group_id)->toBe($this->group->id)
            ->and($payment->organisation_id)->toBe($this->organisation->id)
        ->and($this->group->accountingStats->number_payments)->toBe(1)
        ->and($this->group->accountingStats->number_payments_type_payment)->toBe(1)
        ->and($this->group->accountingStats->number_payments_state_in_process)->toBe(1)
        ->and($this->organisation->accountingStats->number_payments)->toBe(1)
        ->and($this->organisation->accountingStats->number_payments_type_payment)->toBe(1)
        ->and($this->organisation->accountingStats->number_payments_state_in_process)->toBe(1);

        return $payment;
    }
)->depends('create payment account');
