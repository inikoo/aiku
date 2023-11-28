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
use App\Actions\Auth\User\StoreUser;
use App\Actions\Auth\User\UpdateUser;
use App\Actions\Auth\User\UpdateUserStatus;
use App\Actions\Auth\User\UserAddRoles;
use App\Actions\Auth\User\UserRemoveRoles;
use App\Actions\Auth\User\UserSyncRoles;
use App\Actions\Organisation\Group\StoreGroup;
use App\Actions\Organisation\Organisation\StoreOrganisation;
use App\Models\Auth\Guest;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Organisation\Group;
use App\Models\Organisation\Organisation;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

beforeAll(function () {
    loadDB('test_base_database.dump');
});


beforeEach(function () {
    $organisation = Organisation::first();
    if (!$organisation) {
        $group  = StoreGroup::make()->action(
            array_merge(
                Group::factory()->definition(),
                [
                    'code' => 'ACME'
                ]
            )
        );
        $organisation = StoreOrganisation::make()->action(
            $group,
            array_merge(
                Organisation::factory()->definition(),
                [
                    'code' => 'AGB'
                ]
            )
        );
        StoreOrganisation::make()->action(
            $group,
            array_merge(
                Organisation::factory()->definition(),
                [
                    'code' => 'AUS'
                ]
            )
        );
    }
    $this->organisation=$organisation;
});

test('roles are seeded', function () {
    expect(Role::count())->toBe(27);
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
    $guest = UpdateGuest::make()->action($guest, ['contact_name' => 'Pika']);
    expect($guest->contact_name)->toBe('Pika');

    $guest = UpdateGuest::make()->action($guest, ['contact_name' => 'Aiku']);
    expect($guest->contact_name)->toBe('Aiku');
})->depends('create guest');

test('create user for guest', function ($guest) {
    Hash::shouldReceive('make')->andReturn('1234567');
    /** @noinspection PhpUndefinedMethodInspection */
    Hash::makePartial();
    $userData  = User::factory()->definition();

    $user = StoreUser::make()->action($guest, $userData);
    expect($user)->toBeInstanceOf(User::class)
        ->and($user->password)->toBe('1234567')
        ->and($user->parent)->toBeInstanceOf(Guest::class);

    return $user;
})->depends('create guest');

test('fail to create user for guest with invalid usernames', function () {

    $guestData = Guest::factory()->definition();
    $guest2    = StoreGuest::make()->action(
        $guestData
    );


    $userData = User::factory()->definition();
    Arr::set($userData, 'username', 'create');

    expect(function () use ($guest2, $userData) {
        StoreUser::make()->action($guest2, $userData);
    })->toThrow(ValidationException::class);

    Arr::set($userData, 'username', 'export');
    expect(function () use ($guest2, $userData) {
        StoreUser::make()->action($guest2, $userData);
    })->toThrow(ValidationException::class);

});


test('update user password', function ($user) {
    Hash::shouldReceive('make')
        ->andReturn('hello1234');
    /** @noinspection PhpUndefinedMethodInspection */
    Hash::makePartial();

    $user = UpdateUser::make()->action($user, [
        'password' => 'secret'
    ]);

    expect($user->password)->toBe('hello1234');

    return $user;
})->depends('create user for guest');

test('update user username', function ($user) {
    expect($user->username)->toBe('hello');
    $user = UpdateUser::make()->action($user, [
        'username' => 'aiku'
    ]);

    expect($user->username)->toBe('aiku')
        ->and($user->status)->toBeTrue();

    return $user;
})->depends('create user for guest');




test('add user roles', function ($user) {
    expect($user->hasRole(['super-admin', 'system-admin']))->toBeFalse();

    $user = UserAddRoles::make()->action($user, ['super-admin', 'system-admin']);

    expect($user->hasRole(['super-admin', 'system-admin']))->toBeTrue();
})->depends('create user for guest');

test('remove user roles', function ($user) {
    $user = UserRemoveRoles::make()->action($user, ['super-admin', 'system-admin']);

    expect($user->hasRole(['super-admin', 'system-admin']))->toBeFalse();
})->depends('create user for guest');

test('sync user roles', function ($user) {
    $user = UserSyncRoles::make()->action($user, ['super-admin', 'system-admin']);

    expect($user->hasRole(['super-admin', 'system-admin']))->toBeTrue();
})->depends('create user for guest');



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
