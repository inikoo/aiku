<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Apr 2023 16:05:15 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Mail\DispatchedEmail\StoreDispatchEmail;
use App\Actions\Mail\DispatchedEmail\UpdateDispatchedEmail;
use App\Actions\Mail\Mailshot\StoreMailshot;
use App\Actions\Mail\Mailshot\UpdateMailshot;
use App\Actions\Market\Shop\StoreShop;
use App\Actions\Tenancy\Group\StoreGroup;
use App\Actions\Tenancy\Tenant\StoreTenant;
use App\Models\Mail\Mailshot;
use App\Models\Market\Shop;
use App\Models\Tenancy\Group;
use App\Models\Tenancy\Tenant;

beforeAll(function () {
    loadDB('test_base_database.dump');
});


beforeEach(function () {
    $tenant = Tenant::first();
    if (!$tenant) {
        $group  = StoreGroup::make()->asAction(Group::factory()->definition());
        $tenant = StoreTenant::make()->action($group, Tenant::factory()->definition());
    }
    $tenant->makeCurrent();
});

test('get outbox from shop', function () {
    $shop   = StoreShop::make()->action(Shop::factory()->definition());
    $outbox = $shop->outboxes()->first();
    $this->assertModelExists($outbox);

    return $outbox;
});


test('create mailshot', function ($outbox) {
    $mailshot = StoreMailshot::make()->action($outbox, Mailshot::factory()->definition());
    $this->assertModelExists($mailshot);

    return $mailshot;
})->depends('get outbox from shop');

test('update mailshot', function ($mailshot) {
    $mailshot = UpdateMailshot::make()->action($mailshot, Mailshot::factory()->definition());
    $this->assertModelExists($mailshot);
    return $mailshot;
})->depends('create mailshot');

test('create dispatched email in outbox', function ($outbox) {
    $dispatchedEmail = StoreDispatchEmail::make()->action(
        $outbox,
        fake()->email,
        []
    );
    $this->assertModelExists($dispatchedEmail);
})->depends('get outbox from shop');

test('create dispatched email in mailshot', function ($mailshot) {
    $dispatchedEmail = StoreDispatchEmail::make()->action(
        $mailshot,
        fake()->email,
        []
    );
    $this->assertModelExists($dispatchedEmail);

    return $dispatchedEmail;
})->depends('create mailshot');


test('update dispatched email', function ($dispatchedEmail) {
    $updatedDispatchEmail = UpdateDispatchedEmail::make()->action(
        $dispatchedEmail,
        []
    );
    $this->assertModelExists($updatedDispatchEmail);
    return $updatedDispatchEmail;
})->depends('create dispatched email in mailshot');
