<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Apr 2023 16:05:15 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Accounting\PaymentServiceProvider\StorePaymentServiceProvider;
use App\Actions\Accounting\PaymentServiceProvider\UpdatePaymentServiceProvider;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Tenancy\Tenant;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $this->tenant = Tenant::where('slug', 'agb')->first();
    $this->tenant->makeCurrent();

});

test('create payment service provider', function () {
    $paymentServiceProvider = StorePaymentServiceProvider::make()->action(PaymentServiceProvider::factory()->definition());
    $this->assertModelExists($paymentServiceProvider);
    return $paymentServiceProvider;
});

test('update payment service provider', function ($paymentServiceProvider) {
    // $warehouse = Warehouse::find(1);
    $paymentServiceProvider = UpdatePaymentServiceProvider::make()->action($paymentServiceProvider, ['name' => 'Pika Ltd']);
    expect($paymentServiceProvider->name)->toBe('Pika Ltd');
})->depends('create create payment service provider');
