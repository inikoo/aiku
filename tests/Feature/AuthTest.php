<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Apr 2023 16:05:15 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Auth\Guest\StoreGuest;
use App\Actions\Auth\Guest\UpdateGuest;
use App\Actions\Auth\User\StoreUser;
use App\Actions\Auth\User\UpdateUser;
use App\Models\Auth\Guest;
use App\Models\Auth\User;
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

test('update guest', function ($guest) {
    $guest = UpdateGuest::make()->action($guest, ['name' => 'Pika']);
    expect($guest->name)->toBe('Pika');
})->depends('create guest');

test('create user', function () {
    $user = StoreUser::make()->action(User::factory()->definition());
    $this->assertModelExists($user);
    return $user;
});

test('update user', function ($user) {
    $user = UpdateUser::make()->action($user, ['id' => 'AWA28ES']);
    expect($user->id)->toBe('AWA28ES');
})->depends('create user');
