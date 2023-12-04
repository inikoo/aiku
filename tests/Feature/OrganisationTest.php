<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 21 Apr 2023 16:21:46 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Grouping\Group\StoreGroup;
use App\Actions\Grouping\Organisation\StoreOrganisation;
use App\Actions\SysAdmin\Admin\StoreAdmin;
use App\Actions\SysAdmin\SysUser\CreateSysUserAccessToken;
use App\Actions\SysAdmin\SysUser\StoreSysUser;
use App\Models\Mail\Mailroom;
use App\Models\Grouping\Group;
use App\Models\SysAdmin\Admin;
use App\Models\SysAdmin\SysUser;
use App\Models\Grouping\Organisation;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use App\Models\Assets\Currency;

beforeAll(function () {
    loadDB('test_base_database.dump');
    Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

});

test('create group', function () {

    $modelData=Group::factory()->definition();

    $modelData =array_merge($modelData, [
        'code'        => 'TEST',
        'name'        => 'Test Group',
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
