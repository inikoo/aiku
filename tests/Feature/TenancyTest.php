<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 21 Apr 2023 16:21:46 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\SysAdmin\Admin\StoreAdmin;
use App\Actions\SysAdmin\SysUser\CreateSysUserAccessToken;
use App\Actions\SysAdmin\SysUser\StoreSysUser;
use App\Actions\Tenancy\Group\StoreGroup;
use App\Actions\Tenancy\Tenant\StoreTenant;
use App\Enums\Mail\Mailroom\MailroomCodeEnum;
use App\Models\Mail\Mailroom;
use App\Models\SysAdmin\Admin;
use App\Models\SysAdmin\SysUser;
use App\Models\Tenancy\Tenant;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

beforeAll(function () {
    loadDB('test_base_database.dump');
    Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
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

    $token   = CreateSysUserAccessToken::run($sysUser, 'admin', ['*']);
    expect($token)->toBeString();
});


test('create group using action', function () {
    $arrayData= [
        'code'        => 'AB',
        'name'        => 'Test group',
        'currency_id' => '1',
    ];

    $createdGroup = StoreGroup::make()->action($arrayData);

    expect($createdGroup->code)->toBe($arrayData['code']);

    return $createdGroup;
});


test('create group using command', function () {
    $this->artisan('create:group acme "Acme Inc" USD')->assertSuccessful();
});


test('add tenant to group', function ($group) {
    $arrayData = [
        'code'        => 'ABA',
        'name'        => 'Testing Tenant',
        'email'       => 'tenant@email.com',
        'currency_id' => 1,
        'country_id'  => 1,
        'language_id' => 1,
        'timezone_id' => 1,
    ];

    $addedTenant = StoreTenant::make()->action($group, $arrayData);

    expect($addedTenant->code)->toBe($arrayData['code'])->and($addedTenant->name)->toBe($arrayData['name']);

    return $addedTenant;
})->depends('create group using action');

test('tenant has correct mailrooms', function ($tenant) {

    $tenant->makeCurrent();

    $mailrooms= Mailroom::all();
    expect($mailrooms->count())->toBe(3);

    $mailroomCustomerNotifications=Mailroom::where('code', MailroomCodeEnum::CUSTOMER_NOTIFICATION)->firstOrFail();
    $mailroomMarketing            =Mailroom::where('code', MailroomCodeEnum::MARKETING)->firstOrFail();
    $mailroomUserNotifications    =Mailroom::where('code', MailroomCodeEnum::USER_NOTIFICATION)->firstOrFail();

    expect($mailroomCustomerNotifications->code)->toBe(MailroomCodeEnum::CUSTOMER_NOTIFICATION->value)
        ->and($mailroomMarketing->code)->toBe(MailroomCodeEnum::MARKETING->value)
        ->and($mailroomUserNotifications->code)->toBe(MailroomCodeEnum::USER_NOTIFICATION->value);

})->depends('add tenant to group');


test('try to create group with wrong currency', function () {
    $this->artisan('create:group fail "Fail Inc" XXX')->assertFailed();
});

test('try to create group with duplicated code', function () {
    $this->artisan('create:group fail "Fail Inc" XXX')->assertFailed();
});


test('create tenant sys-user', function ($tenant) {
    $arrayData = [
        'username' => 'aiku',
        'password' => 'hello1234',
        'email'    => 'aiku@email.com'
    ];
    $sysUser=StoreSysUser::make()->action($tenant, $arrayData);

    expect($sysUser->userable)->toBeInstanceOf(Tenant::class)
        ->and($sysUser->username)->toBe($arrayData['username']);

    return $sysUser;
})->depends('add tenant to group');
