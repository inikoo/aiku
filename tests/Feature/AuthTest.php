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
use App\Actions\fromIris;
use App\Models\Auth\GroupUser;
use App\Models\Auth\Guest;
use App\Models\Auth\User;
use App\Models\Tenancy\Tenant;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();
});

test('create guest', function () {
    $guestData = Guest::factory()->definition();
    Arr::set($guestData, 'phone', '+6281212121212');
    $guest = StoreGuest::make()->action(
        $guestData
    );
    $this->assertModelExists($guest);

    return $guest;
});

test('update guest', function ($guest) {
    $guest = UpdateGuest::make()->action($guest, ['name' => 'Pika']);
    expect($guest->name)->toBe('Pika');
})->depends('create guest');

test('create user for guest', function ($guest) {
    Hash::shouldReceive('make')
        ->andReturn('1234567');


    $userData = User::factory()->definition();
    Arr::set($userData, 'email', $guest->email);
    $user = StoreUser::make()->action($guest, null, $userData);
    $this->assertModelExists($user);
    expect($user->password)->toBe('1234567')
        ->and($user->groupUser->password)->toBe('1234567');

    return $user;
})->depends('create guest');

test('update user password', function ($user) {
    $userFactory = User::factory()->definition();

    Hash::shouldReceive('make')
        ->andReturn($userFactory['password']);
    $user = UpdateUser::make()->action($user, $userFactory);

    $groupUser = $user->groupUser()->first();

    expect($user->password)->toBe($userFactory['password'])
        ->and($groupUser->username)->toBe($userFactory['username'])
        ->and($groupUser->password)->toBe($userFactory['password']);

    return $user;
})->depends('create user for guest');

test('attaching existing group user to another tenant guest', function () {
    $tenant = Tenant::where('slug', 'aus')->first();
    $tenant->makeCurrent();

    $guestData = Guest::factory()->definition();
    Arr::set($guestData, 'phone', '+6081212120000');
    $guest = StoreGuest::make()->action(
        $guestData
    );

    $groupUser = GroupUser::where('username', 'hello')->first();

    $user = StoreUser::make()->action($guest, $groupUser, []);
    $this->assertModelExists($user);
    expect($user->password)->toBe('password');

    return $user;
})->depends('update user password');
