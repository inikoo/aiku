<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Apr 2023 16:05:15 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Mail\Mailroom\StoreMailroom;
use App\Actions\Mail\Mailroom\UpdateMailroom;
use App\Actions\Mail\Mailshot\StoreMailshot;
use App\Actions\Mail\Mailshot\UpdateMailshot;
use App\Actions\Mail\Outbox\StoreOutbox;
use App\Actions\Mail\Outbox\UpdateOutbox;
use App\Models\Mail\Mailroom;
use App\Models\Mail\Mailshot;
use App\Models\Mail\Outbox;
use App\Models\Tenancy\Tenant;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();

});

test('create mailroom', function () {
    $mailroom = StoreMailroom::make()->action(Mailroom::factory()->definition());
    $this->assertModelExists($mailroom);
    return $mailroom;
});

test('update mailroom', function ($mailroom) {
    $mailroom = UpdateMailroom::make()->action($mailroom, ['name' => 'Pika Ltd']);
    expect($mailroom->code)->toBe('Pika Ltd');
})->depends('create mailroom');

test('create outbox ', function ($mailroom) {
    $outbox = StoreOutbox::make()->action($mailroom, Outbox::factory()->definition());
    $this->assertModelExists($outbox);
    return $outbox;
})->depends('create mailroom');

test('update outbox', function ($outbox) {
    $outbox = UpdateOutbox::make()->action($outbox, ['name' => 'Pika Ltd']);
    expect($outbox->name)->toBe('Pika Ltd');
})->depends('create outbox');

test('create mailshot', function ($outbox) {
    $mailshot = StoreMailshot::make()->action($outbox, Mailshot::factory()->definition());
    $this->assertModelExists($mailshot);
})->depends('create outbox');

test('update mailshot', function ($mailshot) {
    $mailshot = UpdateMailshot::make()->action($mailshot, ['name' => 'Pika Ltd']);
    expect($mailshot->outbox)->toBe('Pika Ltd');
})->depends('create mailshot');
