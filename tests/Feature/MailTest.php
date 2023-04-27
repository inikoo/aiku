<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Apr 2023 16:05:15 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Mail\Mailroom\StoreMailroom;
use App\Actions\Mail\Mailroom\UpdateMailroom;
use App\Models\Mail\Mailroom;
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
