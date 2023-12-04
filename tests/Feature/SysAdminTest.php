<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 21 Apr 2023 16:21:46 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\SysAdmin\Admin\StoreAdmin;
use App\Actions\SysAdmin\Group\StoreGroup;
use App\Actions\SysAdmin\Guest\StoreGuest;
use App\Actions\SysAdmin\Guest\UpdateGuest;
use App\Actions\SysAdmin\Organisation\StoreOrganisation;
use App\Actions\SysAdmin\SysUser\CreateSysUserAccessToken;
use App\Actions\SysAdmin\SysUser\StoreSysUser;
use App\Actions\SysAdmin\User\DeleteUser;
use App\Actions\SysAdmin\User\UpdateUser;
use App\Actions\SysAdmin\User\UpdateUserStatus;
use App\Actions\SysAdmin\User\UserAddRoles;
use App\Actions\SysAdmin\User\UserRemoveRoles;
use App\Actions\SysAdmin\User\UserSyncRoles;
use App\Models\Assets\Currency;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Mail\Mailroom;
use App\Models\SysAdmin\Admin;
use App\Models\SysAdmin\Guest;
use App\Models\SysAdmin\Role;
use App\Models\SysAdmin\SysUser;
use App\Models\SysAdmin\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\{get};
use function Pest\Laravel\{actingAs};

beforeAll(function () {
    loadDB('test_base_database.dump');
    Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
});

test('create group', function () {
    $modelData = Group::factory()->definition();

    $modelData = array_merge($modelData, [
        'code' => 'TEST',
        'name' => 'Test Group',
    ]);

    $group = StoreGroup::make()->action($modelData);
    expect($group)->toBeInstanceOf(Group::class);

    return $group;
});

test('create group by command', function () {
    $this->artisan('group:create', [
        'code'          => 'TEST2',
        'name'          => 'Test Group',
        'currency_code' => Currency::where('code', 'USD')->firstOrFail()->code,
        'country_code'  => 'US'
    ])->assertSuccessful();
    $group = Group::where('code', 'TEST')->firstOrFail();
    expect($group)->toBeInstanceOf(Group::class);

    return $group;
});


test('mailrooms seeded correctly', function () {
    $mailrooms = Mailroom::all();
    expect($mailrooms->count())->toBe(6);
});

test('create a system admin', function () {
    $admin = StoreAdmin::make()->action(Admin::factory()->definition());
    $this->assertModelExists($admin);
});

test('create a system admin user', function () {
    $admin   = StoreAdmin::make()->action(Admin::factory()->definition());
    $sysUser = StoreSysUser::make()->action($admin, SysUser::factory()->definition());
    $this->assertModelExists($sysUser);
    expect($sysUser)->toBeInstanceOf(SysUser::class)
        ->and($sysUser->userable)->toBeInstanceOf(Admin::class);
});

test('create a system admin user access token', function () {
    $admin   = StoreAdmin::make()->action(Admin::factory()->definition());
    $sysUser = StoreSysUser::make()->action($admin, SysUser::factory()->definition());

    $token = CreateSysUserAccessToken::run($sysUser, 'admin', ['*']);
    expect($token)->toBeString();
});

test('create organisation', function (Group $group) {
    $modelData    = Organisation::factory()->definition();
    $organisation = StoreOrganisation::make()->action($group, $modelData);
    expect($organisation)->toBeInstanceOf(Organisation::class);

    return $organisation;
})->depends('create group');

test('create organisation by command', function () {
    $this->artisan('org:create', [
        'group'         => 'TEST2',
        'code'          => 'TEST',
        'email'         => 'a@example.com',
        'name'          => 'Test Organisation in group 2',
        'country_code'  => 'MY',
        'currency_code' => 'MYR',
    ])->assertSuccessful();
    $organisation = Organisation::where('code', 'TEST')->firstOrFail();
    expect($organisation)->toBeInstanceOf(Organisation::class);

    return $organisation;
})->depends('create group by command');

test('create organisation sys-user', function ($organisation) {
    $arrayData = [
        'username' => 'aiku',
        'password' => 'hello1234',
        'email'    => 'aiku@email.com'
    ];
    $sysUser   = StoreSysUser::make()->action($organisation, $arrayData);

    expect($sysUser->userable)->toBeInstanceOf(Organisation::class)
        ->and($sysUser->username)->toBe($arrayData['username']);

    return $sysUser;
})->depends('create organisation');

test('roles are seeded', function () {
    expect(Role::count())->toBe(27);
});

test('create guest', function (Group $group) {
    $guestData = Guest::factory()->definition();
    data_set($guestData, 'username', 'hello');
    data_set($guestData, 'phone', '+6281212121212');
    $guest = StoreGuest::make()->action(
        $group,
        $guestData
    );

    expect($guest)->toBeInstanceOf(Guest::class)
        ->and($guest->user)->toBeInstanceOf(User::class)
        ->and($guest->user->username)->toBe('hello')
        ->and($guest->user->contact_name)->toBe($guest->contact_name)
        ->and($guest->phone)->toBe('+6281212121212')
        ->and($group->sysadminStats->number_guests)->toBe(1)
        ->and($group->sysadminStats->number_guests_status_active)->toBe(1)
        ->and($group->sysadminStats->number_users)->toBe(1)
        ->and($group->sysadminStats->number_users_status_active)->toBe(1)
        ->and($group->sysadminStats->number_users_status_inactive)->toBe(0)
        ->and($group->sysadminStats->number_users_type_guest)->toBe(1);

    return $guest;
})->depends('create group');

test('create guest from command', function (Group $group) {
    $this->artisan(
        'guest:create',
        [
            'group'      => $group->code,
            'name'       => 'Pika',
            'alias'      => 'pika',
            '--password' => 'hello1234',
            '--email'    => 'pika@inikoo.com',
            '--position' => 'admin'
        ]
    )->assertSuccessful();
})->depends('create group');

test('update guest', function ($guest) {
    $guest = UpdateGuest::make()->action($guest, ['contact_name' => 'Pika']);
    expect($guest->contact_name)->toBe('Pika');

    $guest = UpdateGuest::make()->action($guest, ['contact_name' => 'Aiku']);
    expect($guest->contact_name)->toBe('Aiku');
})->depends('create guest');

test('fail to create guest with invalid usernames', function (Group $group) {
    $guestData = Guest::factory()->definition();

    data_set($guestData, 'username', 'create');
    expect(function () use ($guestData, $group) {
        StoreGuest::make()->action(
            $group,
            $guestData
        );
    })->toThrow(ValidationException::class);

    data_set($guestData, 'username', 'export');
    expect(function () use ($guestData, $group) {
        StoreGuest::make()->action(
            $group,
            $guestData
        );
    })->toThrow(ValidationException::class);
})->depends('create group');


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
        'username' => 'new-username'
    ]);

    expect($user->username)->toBe('new-username')
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

test('delete user', function ($user) {
    expect($user)->toBeInstanceOf(User::class);
    $user = DeleteUser::make()->action($user);
    expect($user->deleted_at)->toBeInstanceOf(Carbon::class);
})->depends('update user password');

test('can show hr dashboard', function (Guest $guest) {
    actingAs($guest->user);
    $response = get(route('grp.sysadmin.dashboard'));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('SysAdmin/SysAdminDashboard')
            ->has('breadcrumbs', 2)
            ->where('stats.0.stat', 1)->where('stats.0.href.name', 'grp.sysadmin.users.index')
            ->where('stats.1.stat', 1)->where('stats.1.href.name', 'grp.sysadmin.guests.index');
    });
})->depends('create guest')->todo();
