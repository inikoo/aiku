<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 21 Apr 2023 16:21:46 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Helpers\Avatars\GetDiceBearAvatar;
use App\Actions\SysAdmin\Admin\StoreAdmin;
use App\Actions\SysAdmin\Group\StoreGroup;
use App\Actions\SysAdmin\Group\UpdateGroup;
use App\Actions\SysAdmin\Guest\DeleteGuest;
use App\Actions\SysAdmin\Guest\StoreGuest;
use App\Actions\SysAdmin\Guest\UpdateGuest;
use App\Actions\SysAdmin\Organisation\StoreOrganisation;
use App\Actions\SysAdmin\Organisation\UpdateOrganisation;
use App\Actions\SysAdmin\User\UpdateUser;
use App\Actions\SysAdmin\User\UpdateUserStatus;
use App\Actions\SysAdmin\User\UserAddRoles;
use App\Actions\SysAdmin\User\UserRemoveRoles;
use App\Actions\SysAdmin\User\UserSyncRoles;
use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Models\Helpers\Currency;
use App\Models\SysAdmin\Admin;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Guest;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\Role;
use App\Models\SysAdmin\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\{get};
use function Pest\Laravel\{actingAs};

beforeAll(function () {
    loadDB();
    Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
});

beforeEach(function () {
    GetDiceBearAvatar::mock()
        ->shouldReceive('handle')
        ->andReturn(Storage::disk('art')->get('avatars/shapes.svg'));


    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
});

test('create group', function () {
    $modelData = Group::factory()->definition();

    $modelData = array_merge($modelData, [
        'code' => 'TEST',
        'name' => 'Test Group',
    ]);

    $group = StoreGroup::make()->action($modelData);
    expect($group)->toBeInstanceOf(Group::class)
        ->and($group->roles()->count())->toBe(5);

    return $group;
});

test('set group logo by command', function (Group $group) {
    $this->artisan('group:logo', [
        'group' => $group->slug,
    ])->assertSuccessful();
})->depends('create group');

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

test('update group name', function (Group $group) {
    $group = UpdateGroup::make()->action($group, ['name' => 'Test Group 2']);
    expect($group->name)->toBe('Test Group 2');
})->depends('create group');



test('create a system admin', function () {
    $admin = StoreAdmin::make()->action(Admin::factory()->definition());
    $this->assertModelExists($admin);
});


test('create organisation type shop', function (Group $group) {
    $modelData = Organisation::factory()->definition();
    data_set($modelData, 'code', 'acme');
    data_set($modelData, 'type', OrganisationTypeEnum::SHOP);

    $organisation = StoreOrganisation::make()->action($group, $modelData);

    expect($organisation)->toBeInstanceOf(Organisation::class)
        ->and($organisation->roles()->count())->toBe(7)
        ->and($group->roles()->count())->toBe(12)
        ->and($organisation->accountingStats->number_org_payment_service_providers)->toBe(1)
        ->and($organisation->accountingStats->number_org_payment_service_providers_type_account)->toBe(1);

    return $organisation;
})->depends('create group');

test('set organisation logo by command', function (Organisation $organisation) {
    $this->artisan('org:logo', [
        'organisation' => $organisation->slug,
    ])->assertSuccessful();
})->depends('create organisation type shop');

test('create organisation by command', function (Group $group) {
    $this->artisan('org:create', [
        'group'         => $group->slug,
        'type'          => 'shop',
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

test('update organisation name', function (Organisation $organisation) {
    $organisation = UpdateOrganisation::make()->action(
        $organisation,
        ['name' => 'Test New Organisation 2']
    );
    expect($organisation->name)->toBe('Test New Organisation 2');
})->depends('create organisation by command');

test('set organisation google key', function (Organisation $organisation) {
    $this->artisan('org:set-google-key', [
        'organisation'               => $organisation->slug,
        'google_cloud_client_id'     => '1234567890',
        'google_cloud_client_secret' => '1234567890',
        'google_drive_folder_key'    => '1234567890'
    ])->assertSuccessful();
})->depends('create organisation by command');


test('roles are seeded', function () {
    expect(Role::count())->toBe(19);
});

test('create guest', function (Group $group, Organisation $organisation) {
    app()->instance('group', $group);
    setPermissionsTeamId($group->id);

    $guestData = Guest::factory()->definition();
    data_set($guestData, 'username', 'hello');
    data_set($guestData, 'password', 'secret-password');
    data_set($guestData, 'phone', '+6281212121212');
    data_set($guestData, 'roles', ['supply-chain', 'org-shop-admin-'.$organisation->id]);

    $guest = StoreGuest::make()->action(
        $group,
        $guestData
    );

    expect($guest)->toBeInstanceOf(Guest::class)
        ->and($guest->user)->toBeInstanceOf(User::class)
        ->and($guest->user->username)->toBe('hello')
        ->and($guest->user->contact_name)->toBe($guest->contact_name)
        ->and($guest->user->authorisedOrganisations()->count())->toBe(1)
        ->and($guest->phone)->toBe('+6281212121212')
        ->and($group->sysadminStats->number_guests)->toBe(1)
        ->and($group->sysadminStats->number_guests_status_active)->toBe(1)
        ->and($group->sysadminStats->number_users)->toBe(1)
        ->and($group->sysadminStats->number_users_status_active)->toBe(1)
        ->and($group->sysadminStats->number_users_status_inactive)->toBe(0)
        ->and($group->sysadminStats->number_users_type_guest)->toBe(1);


    return $guest;
})->depends('create group', 'create organisation type shop');

test('UserHydrateAuthorisedModels command', function (Guest $guest) {
    $this->artisan('user:hydrate-authorised-models', [
        'user' => $guest->user->slug,
    ])->assertSuccessful();
})->depends('create guest');

test('create guest from command', function (Group $group) {
    expect($group->sysadminStats->number_guests)->toBe(1);
    $this->artisan(
        'guest:create',
        [
            'group'      => $group->slug,
            'name'       => 'Mr Pika',
            'username'   => 'pika',
            '--password' => 'hello1234',
            '--email'    => 'pika@inikoo.com',
            '--roles'    => 'super-admin'
        ]
    )->assertSuccessful();

    /** @var Guest $guest */
    $guest = $group->guests()->where('alias', 'pika')->firstOrFail();
    $group->refresh();
    expect($guest->user->username)->toBe('pika')
        ->and($group->sysadminStats->number_guests)->toBe(2)
        ->and($group->sysadminStats->number_guests_status_active)->toBe(2)
        ->and($group->sysadminStats->number_users)->toBe(2);

    return $guest;
})->depends('create group');

test('update guest', function ($guest) {
    app()->instance('group', $guest->group);
    setPermissionsTeamId($guest->group->id);
    $guest = UpdateGuest::make()->action($guest, ['contact_name' => 'Wow']);
    expect($guest->contact_name)->toBe('Wow');

    $guest = UpdateGuest::make()->action($guest, ['contact_name' => 'John']);
    expect($guest->contact_name)->toBe('John');

    return $guest;
})->depends('create guest from command');

test('fail to create guest with invalid usernames', function (Group $group) {
    app()->instance('group', $group);
    setPermissionsTeamId($group->id);

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
    $user = UpdateUser::make()->action($guest->user, [
        'password' => 'secret'
    ]);

    expect(Hash::check('secret', $user->password))->toBeTrue();

    return $user;
})->depends('update guest');

test('update user username', function (User $user) {
    expect($user->username)->toBe('pika');
    $user = UpdateUser::make()->action($user, [
        'username' => 'new-username'
    ]);

    expect($user->username)->toBe('new-username')
        ->and($user->status)->toBeTrue();

    return $user;
})->depends('update user password');

test('add user roles', function ($guest) {
    app()->instance('group', $guest->group);
    setPermissionsTeamId($guest->group->id);
    expect($guest->user->hasRole(['supply-chain']))->toBeTrue();

    $user = UserAddRoles::make()->action($guest->user, ['system-admin']);

    expect($user->hasRole(['supply-chain', 'system-admin']))->toBeTrue();
})->depends('create guest');

test('remove user roles', function ($guest) {
    app()->instance('group', $guest->group);
    setPermissionsTeamId($guest->group->id);
    $user = UserRemoveRoles::make()->action($guest->user, ['super-admin', 'system-admin']);

    expect($user->hasRole(['super-admin', 'system-admin']))->toBeFalse();
})->depends('create guest');

test('sync user roles', function ($guest) {
    app()->instance('group', $guest->group);
    setPermissionsTeamId($guest->group->id);
    $user = UserSyncRoles::make()->action($guest->user, ['super-admin', 'system-admin']);

    expect($user->hasRole(['super-admin', 'system-admin']))->toBeTrue();
})->depends('create guest');


test('user status change', function ($user) {
    expect($user->status)->toBeTrue();
    $user = UpdateUserStatus::make()->action($user, false);
    expect($user->status)->toBeFalse();
})->depends('update user password');

test('delete guest', function ($user) {
    $guest = DeleteGuest::make()->action($user->parent);
    expect($guest->deleted_at)->toBeInstanceOf(Carbon::class);
})->depends('update user password');



test('can show app login', function () {
    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    $response = get(route('grp.login.show'));

    $response->assertInertia(function (AssertableInertia $page) {
        $page->component('SysAdmin/Login');
    });
});

test('can not login with wrong credentials', function (Guest $guest) {
    $response = $this->post(route('grp.login.store'), [
        'username' => $guest->user->username,
        'password' => 'wrong password',
    ]);

    $response->assertRedirect('http://app.'.config('app.domain'));
    $response->assertSessionHasErrors('username');

    $user = $guest->user;
    $user->refresh();
    expect($user->stats->number_failed_logins)->toBe(1);
})->depends('create guest');

test('can login', function (Guest $guest) {
    $response = $this->post(route('grp.login.store'), [
        'username' => $guest->user->username,
        'password' => 'secret-password',
    ]);
    $response->assertRedirect(route('grp.dashboard.show'));
    $this->assertAuthenticatedAs($guest->user);

    $user = $guest->user;
    $user->refresh();
    expect($user->stats->number_logins)->toBe(1);
})->depends('create guest');

test('can show hr dashboard', function (Guest $guest) {
    $group = $guest->group;
    app()->instance('group', $guest->group);
    setPermissionsTeamId($group->id);

    $guest = StoreGuest::make()->action(
        $group,
        Guest::factory()->definition()
    );

    actingAs($guest->user);

    $response = get(route('grp.sysadmin.dashboard'));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('SysAdmin/SysAdminDashboard')
            ->has('breadcrumbs', 2)
            ->where('stats.0.stat', 2)->where('stats.0.href.name', 'grp.sysadmin.users.index')
            ->where('stats.1.stat', 2)->where('stats.1.href.name', 'grp.sysadmin.guests.index');
    });
})->depends('create guest');

test('Hydrate group via command', function (Group $group) {
    $this->artisan('hydrate:group '.$group->slug)->assertSuccessful();

})->depends('create group');

test('Hydrate organisations via command', function (Organisation $organisation) {
    $this->artisan('hydrate:organisations '.$organisation->slug)->assertSuccessful();
})->depends('create organisation type shop');
