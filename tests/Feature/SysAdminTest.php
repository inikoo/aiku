<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 21 Apr 2023 16:21:46 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Helpers\Address\HydrateAddress;
use App\Actions\Helpers\Address\ParseCountryID;
use App\Actions\Helpers\Avatars\GetDiceBearAvatar;
use App\Actions\Helpers\Country\UI\GetAddressData;
use App\Actions\Helpers\Country\UI\GetCountriesOptions;
use App\Actions\Helpers\Currency\UI\GetCurrenciesOptions;
use App\Actions\Helpers\Language\UI\GetLanguagesOptions;
use App\Actions\Helpers\Media\HydrateMedia;
use App\Actions\Helpers\ReindexSearch;
use App\Actions\Helpers\TimeZone\UI\GetTimeZonesOptions;
use App\Actions\HumanResources\Employee\CreateUserFromEmployee;
use App\Actions\HumanResources\Employee\StoreEmployee;
use App\Actions\HumanResources\Employee\UpdateEmployeeOtherOrganisationJobPositions;
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
use App\Models\Helpers\Address;
use App\Models\Helpers\Country;
use App\Models\Helpers\Currency;
use App\Models\Helpers\Media;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\JobPosition;
use App\Models\SysAdmin\Admin;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Guest;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\Role;
use App\Models\SysAdmin\User;
use Illuminate\Http\UploadedFile;
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
        ->andReturn(Storage::disk('art')->get('icons/shapes.svg'));


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
        ->and($group->roles()->count())->toBe(5)
        ->and($group->webBlockTypeCategories()->count())->toBe(11)
    ->and($group->webBlockTypes()->count())->toBe(18);

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

    $address = new Address(Address::factory()->definition());
    data_set($modelData, 'address', $address->toArray());


    $organisation = StoreOrganisation::make()->action($group, $modelData);

    expect($organisation)->toBeInstanceOf(Organisation::class)
        ->and($organisation->address)->toBeInstanceOf(Address::class)
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

test('update organisation logo', function (Organisation $organisation) {
    Storage::fake('public');

    $fakeImage = UploadedFile::fake()->image('logo.jpg', 20, 20);

    $organisation = UpdateOrganisation::make()->action(
        $organisation,
        [
            'logo' => $fakeImage
        ]
    );
    $organisation->refresh();
    expect($organisation->images->count())->toBe(1)
        ->and($organisation->image->name)->toBe('logo.jpg');
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

    return $guest;
})->depends('create guest');

test('Hydrate group via command', function (Group $group) {
    $this->artisan('hydrate:group '.$group->slug)->assertSuccessful();
})->depends('create group');

test('Hydrate organisations via command', function (Organisation $organisation) {
    $this->artisan('hydrate:organisations '.$organisation->slug)->assertSuccessful();
})->depends('create organisation type shop');

test('seed stock images', function () {
    $this->artisan('group:seed-stock-images')->assertSuccessful();
});

test('hydrate media', function (Group $group) {
    /** @var Media $media */
    $media = $group->images()->first();
    HydrateMedia::run($media);
    $this->artisan('hydrate:medias')->assertSuccessful();

    $media->refresh();
    expect($media->usage)->toBe(1)
        ->and($media->multiplicity)->toBe(2);
})->depends('create group');

test('can show media', function (Guest $guest) {
    $group = $guest->group;
    app()->instance('group', $guest->group);
    setPermissionsTeamId($group->id);

    /** @var Media $media */
    $media = $group->images()->where('mime_type', 'image/png')->first();
    actingAs($guest->user);
    $response = get(route('grp.media.show', $media->ulid));
    $response->assertOk();
    $response->assertHeader('Content-Type', 'image/png');
})->depends('create guest');

test('hydrate address', function (Organisation $organisation) {
    $address = $organisation->address;
    HydrateAddress::run($address);
    $this->artisan('hydrate:addresses')->assertSuccessful();

    $address->refresh();
    expect($address->usage)->toBe(1)
        ->and($address->multiplicity)->toBe(1);
})->depends('create organisation type shop');

test('parse country', function () {
    $countryId = ParseCountryID::run('malaysia');
    $country   = Country::find($countryId);
    expect($country->code)->toBe('MY');

    $countryId = ParseCountryID::run('DEU');
    $country   = Country::find($countryId);
    expect($country->code)->toBe('DE');
});

test('get helpers select options data', function () {
    $countryData = GetAddressData::run();
    expect($countryData)->toHaveCount(259);
    $countryDataBis = GetCountriesOptions::run();
    expect($countryDataBis)->toHaveCount(259);
    $currencyData = GetCurrenciesOptions::run();
    expect($currencyData)->toHaveCount(159);
    $timezonesData = GetTimeZonesOptions::run();
    expect($timezonesData)->toHaveCount(419);
    $languagesData = GetLanguagesOptions::make()->all();
    expect($languagesData)->toHaveCount(279);
    $translatedLanguagesData = GetLanguagesOptions::make()->translated();
    expect($translatedLanguagesData)->toHaveCount(8);
});

test('update search', function () {
    $this->artisan('search:reindex')->assertSuccessful();
});

test('update web block types', function (Group $group) {
    $this->artisan('group:seed-web-block-types')->assertSuccessful();
    $group->refresh();
    expect($group->webBlockTypeCategories()->count())->toBe(11)
        ->and($group->webBlockTypes()->count())->toBe(18);
})->depends('create group');

test('show log in', function () {

    $response = $this->get(route('grp.login.show'));
    $response->assertInertia(function (AssertableInertia $page) {
        $page->component('SysAdmin/Login');
    });
});

test('should not show without authentication', function () {
    $response= $this->get(route('grp.dashboard.show'));
    $response->assertStatus(302);
    $response->assertRedirect(route('grp.login.show'));
});

test('reindex search', function () {
    ReindexSearch::run();
});

test('employee job position in another organisation', function () {
    $group = Group::where('slug', 'test')->first();
    $org1 = $group->organisations()->first();
    $org2 = $group->organisations()->skip(1)->first();


    $employee = StoreEmployee::make()->action($org1, Employee::factory()->definition());
    $user = CreateUserFromEmployee::run($employee);

    $jobPosition1 = $org2->jobPositions()->where('code', 'hr-c')->first();

    expect($group->number_organisations)->toBe(2)
        ->and($employee)->toBeInstanceOf(Employee::class)
        ->and($user)->toBeInstanceOf(User::class)
        ->and($jobPosition1)->toBeInstanceOf(JobPosition::class);


    UpdateEmployeeOtherOrganisationJobPositions::make()->action(
        $user,
        [
            'positions' => [
                [
                'slug'  => $jobPosition1->slug,
                'scopes'=> []
                ]
            ]
        ]
    );
});
