<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Apr 2023 16:05:15 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Auth\Guest\StoreGuest;
use App\Actions\Auth\Guest\UpdateGuest;
use App\Actions\Auth\User\DeleteUser;
use App\Actions\Auth\User\UpdateUser;
use App\Actions\Auth\User\UpdateUserStatus;
use App\Actions\Auth\User\UserAddRoles;
use App\Actions\Auth\User\UserRemoveRoles;
use App\Actions\Auth\User\UserSyncRoles;
use App\Models\Auth\Guest;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

beforeAll(function () {
    loadDB('test_base_database.dump');
});


beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->group        = group();
});

test('roles are seeded', function () {
    expect(Role::count())->toBe(27);
});

test('create guest', function () {
    $guestData = Guest::factory()->definition();
    data_set($guestData, 'username', 'hello');
    data_set($guestData, 'phone', '+6281212121212');
    $guest = StoreGuest::make()->action(
        $this->group,
        $guestData
    );

    $this->assertModelExists($guest);

    return $guest;
});

test('update guest', function ($guest) {
    $guest = UpdateGuest::make()->action($guest, ['contact_name' => 'Pika']);
    expect($guest->contact_name)->toBe('Pika');

    $guest = UpdateGuest::make()->action($guest, ['contact_name' => 'Aiku']);
    expect($guest->contact_name)->toBe('Aiku');
})->depends('create guest');



test('fail to create guest with invalid usernames', function () {

    $guestData = Guest::factory()->definition();

    data_set($guestData, 'username', 'create');
    expect(function () use ($guestData) {
        StoreGuest::make()->action(
            $this->group,
            $guestData
        );
    })->toThrow(ValidationException::class);

    data_set($guestData, 'username', 'export');
    expect(function () use ($guestData) {
        StoreGuest::make()->action(
            $this->group,
            $guestData
        );
    })->toThrow(ValidationException::class);

});


test('update user password', function ($guest) {
    Hash::shouldReceive('make')
        ->andReturn('hello1234');
    /** @noinspection PhpUndefinedMethodInspection */
    Hash::makePartial();

    $user = UpdateUser::make()->action($guest->user, [
        'password' => 'secret'
    ]);

    expect($user->password)->toBe('hello1234');

    return $user;
})->depends('create guest');

test('update user username', function ($guest) {
    expect($guest->user->username)->toBe('hello');
    $user = UpdateUser::make()->action($guest->user, [
        'username' => 'aiku'
    ]);

    expect($user->username)->toBe('aiku')
        ->and($user->status)->toBeTrue();

    return $user;
})->depends('create guest');




test('add user roles', function ($guest) {
    expect($guest->user->hasRole(['super-admin', 'system-admin']))->toBeFalse();

    $user = UserAddRoles::make()->action($guest->user, ['super-admin', 'system-admin']);

    expect($user->hasRole(['super-admin', 'system-admin']))->toBeTrue();
})->depends('create guest');

test('remove user roles', function ($guest) {
    $user = UserRemoveRoles::make()->action($guest->user, ['super-admin', 'system-admin']);

    expect($user->hasRole(['super-admin', 'system-admin']))->toBeFalse();
})->depends('create guest');

test('sync user roles', function ($guest) {
    $user = UserSyncRoles::make()->action($guest->user, ['super-admin', 'system-admin']);

    expect($user->hasRole(['super-admin', 'system-admin']))->toBeTrue();
})->depends('create guest');



test('user status change', function ($user) {
    expect($user->status)->toBeTrue();
    $user = UpdateUserStatus::make()->action($user, false);
    expect($user->status)->toBeFalse();
})->depends('update user password');

test('delete group user', function ($user) {
    expect($user)->toBeInstanceOf(User::class);
    $user = DeleteUser::make()->action($user);
    expect($user->deleted_at)->toBeInstanceOf(Carbon::class);
})->depends('update user password');
