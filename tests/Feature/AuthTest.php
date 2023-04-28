<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Apr 2023 16:05:15 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Auth\Guest\StoreGuest;
use App\Actions\Dropshipping\CustomerClient\UpdateCustomerClient;
use App\Models\Auth\Guest;
use App\Models\Tenancy\Tenant;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();

});

test('create guest', function () {
    $guest = StoreGuest::make()->action(Guest::factory()->definition());
    $this->assertModelExists($guest);
    return $guest;
});

test('update guest', function ($customerClient) {
    $customerClient = UpdateCustomerClient::make()->action($customerClient, ['name' => 'Pika']);
    expect($customerClient->name)->toBe('Pika');
})->depends('create customer client');
