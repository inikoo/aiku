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
use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\Mail\EmailTemplate\StoreEmailTemplate;
use App\Actions\Mail\EmailTemplate\UpdateEmailTemplate;
use App\Enums\Mail\Outbox\OutboxTypeEnum;
use App\Models\Mail\Mailshot;
use App\Models\Catalogue\Shop;
use App\Models\Mail\EmailTemplate;
use App\Models\Mail\Outbox;

beforeAll(function () {
    loadDB();
});


beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->group        = $this->organisation->group;
});

test('seed organisation outboxes customers command', function () {
    $this->artisan('org:seed-outboxes '.$this->organisation->slug)->assertExitCode(0);
});

test('post rooms seeded correctly', function () {
    $postRooms = $this->group->postRooms;
    expect($postRooms->count())->toBe(5)
        ->and($this->group->mailStats->number_post_rooms)->toBe(5);
});


test('outbox seeded when shop created', function () {
    $shop   = StoreShop::make()->action($this->organisation, Shop::factory()->definition());
    expect($shop->group->mailStats->number_outboxes)->toBe(12)
        ->and($shop->organisation->mailStats->number_outboxes)->toBe(12)
        ->and($shop->mailStats->number_outboxes)->toBe(11);

    return $shop;

});

test('seed shop outboxes customers command', function (Shop $shop) {
    $this->artisan('shop:seed-outboxes '.$shop->slug)->assertExitCode(0);
})->depends('outbox seeded when shop created');


test('create mailshot', function (Shop $shop) {

    /** @var Outbox $outbox */
    $outbox=$shop->outboxes()->where('type', OutboxTypeEnum::MARKETING)->first();

    $mailshot = StoreMailshot::make()->action($outbox, Mailshot::factory()->definition());
    $this->assertModelExists($mailshot);

    return $mailshot;
})->depends('outbox seeded when shop created');

test('update mailshot', function ($mailshot) {
    $mailshot = UpdateMailshot::make()->action($mailshot, Mailshot::factory()->definition());
    $this->assertModelExists($mailshot);
    return $mailshot;
})->depends('create mailshot');

test('create dispatched email in outbox', function (Shop $shop) {
    /** @var Outbox $outbox */
    $outbox          =$shop->outboxes()->where('type', OutboxTypeEnum::MARKETING)->first();
    $dispatchedEmail = StoreDispatchEmail::make()->action(
        $outbox,
        fake()->email,
        []
    );
    $this->assertModelExists($dispatchedEmail);
})->depends('outbox seeded when shop created');

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

test('store email template', function (Shop $shop) {
    /** @var Outbox $outbox */
    $outbox        = $shop->outboxes()->first();
    $emailTemplate = StoreEmailTemplate::make()->action(
        $this->organisation,
        $outbox,
        [
            'name' => 'temp'
        ]
    );
    expect($emailTemplate)->toBeInstanceOf(EmailTemplate::class)
        ->and($emailTemplate->name)->toBe('temp');

    return $emailTemplate;
})->depends('outbox seeded when shop created');

test('update email template', function (EmailTemplate $emailTemplate) {
    $updatedEmailTemplate = UpdateEmailTemplate::make()->action(
        $emailTemplate,
        [
            'name' => 'tmp'
        ]
    );
    expect($updatedEmailTemplate)->toBeInstanceOf(EmailTemplate::class)
        ->and($updatedEmailTemplate->name)->toBe('tmp');

    return $updatedEmailTemplate;
})->depends('store email template');
