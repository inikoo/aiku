<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 21 Apr 2023 16:21:46 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Tenancy\Group\StoreGroup;
use App\Actions\Tenancy\Tenant\StoreTenant;
use App\Enums\Mail\Mailroom\MailroomCodeEnum;
use App\Models\Mail\Mailroom;
use App\Models\Tenancy\Group;
use App\Models\Tenancy\Tenant;

beforeAll(function () {
    loadDB('d1_fresh_with_assets.dump');
});


test('create group using action', function () {
    $group = StoreGroup::make()->asAction(Group::factory()->definition());

    $this->assertModelExists($group);
});


test('create group using command', function () {
    $this->artisan('create:group acme "Acme Inc" USD')->assertSuccessful();
});


test('add tenant to group', function () {
    $group  = Group::latest()->first();
    $tenant = StoreTenant::make()->action($group, Tenant::factory()->definition());

    $this->assertModelExists($tenant);
    return $tenant;
});

test('tenant has correct mailrooms', function ($tenant) {

    $tenant->makeCurrent();

    $mailrooms= Mailroom::all();
    expect($mailrooms->count())->toBe(3);

    $mailroomCustomerNotifications=Mailroom::where('code', MailroomCodeEnum::CUSTOMER_NOTIFICATION)->firstOrFail();
    $this->assertModelExists($mailroomCustomerNotifications);
    $mailroomMarketing=Mailroom::where('code', MailroomCodeEnum::MARKETING)->firstOrFail();
    $this->assertModelExists($mailroomMarketing);
    $mailroomUserNotifications=Mailroom::where('code', MailroomCodeEnum::USER_NOTIFICATION)->firstOrFail();
    $this->assertModelExists($mailroomUserNotifications);


})->depends('add tenant to group');



test('try to create group with wrong currency', function () {
    $this->artisan('create:group fail "Fail Inc" XXX')->assertFailed();
});

test('try to create group with duplicated code', function () {
    $this->artisan('create:group fail "Fail Inc" XXX')->assertFailed();
});
