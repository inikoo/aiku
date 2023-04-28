<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Apr 2023 16:05:15 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Dropshipping\CustomerClient\StoreCustomerClient;
use App\Actions\Dropshipping\CustomerClient\UpdateCustomerClient;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Tenancy\Tenant;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();

});

test('create customer client', function () {
    $customerClient = StoreCustomerClient::make()->action(CustomerClient::factory()->definition());
    $this->assertModelExists($customerClient);
    return $customerClient;
});

test('update customer client', function ($customerClient) {
    $customerClient = UpdateCustomerClient::make()->action($customerClient, ['name' => 'Pika']);
    expect($customerClient->name)->toBe('Pika');
})->depends('create customer client');
