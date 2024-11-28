<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 26 Nov 2024 21:08:48 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\Comms\Mailshot\StoreMailshot;
use App\Actions\Comms\Mailshot\UpdateMailshot;
use App\Actions\Comms\Outbox\AttachModelToOutbox;
use App\Actions\Comms\Outbox\DetachModelToOutbox;
use App\Actions\Comms\Outbox\StoreOutbox;
use App\Actions\Comms\Outbox\UpdateModelToOutbox;
use App\Actions\Web\Website\StoreWebsite;
use App\Enums\Comms\Outbox\OutboxBlueprintEnum;
use App\Enums\Comms\Outbox\OutboxCodeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Comms\Mailshot;
use App\Models\Comms\ModelSubscribedToOutbox;
use App\Models\Comms\Outbox;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Web\Website;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;

beforeAll(function () {
    loadDB();
});


beforeEach(function () {
    list(
        $this->organisation,
        $this->user,
        $this->shop
    ) = createShop();
    $this->customer = createCustomer($this->shop);
    $this->group    = $this->organisation->group;
});

test('post rooms seeded correctly', function () {

    $postRooms = $this->group->postRooms;
    expect($postRooms->count())->toBe(5)
        ->and($this->group->commsStats->number_post_rooms)->toBe(5);
});

test('seed organisation outboxes customers command', function () {

    $this->artisan('org:seed-outboxes '.$this->organisation->slug)->assertExitCode(0);
    $this->artisan('org:seed-outboxes')->assertExitCode(0);
    expect($this->group->commsStats->number_outboxes)->toBe(12)
        ->and($this->organisation->commsStats->number_outboxes)->toBe(12)
        ->and($this->organisation->commsStats->number_outboxes_type_test)->toBe(1)
        ->and($this->organisation->commsStats->number_outboxes_state_active)->toBe(9);
});

test('outbox seeded when shop created', function () {
    $shop   = StoreShop::make()->action($this->organisation, Shop::factory()->definition());
    expect($shop->group->commsStats->number_outboxes)->toBe(23)
        ->and($shop->organisation->commsStats->number_outboxes)->toBe(23)
        ->and($shop->commsStats->number_outboxes)->toBe(11);

    return $shop;

});

test('seed shop outboxes by command', function (Shop $shop) {
    $this->artisan('shop:seed-outboxes '.$shop->slug)->assertExitCode(0);
    $this->artisan('shop:seed-outboxes')->assertExitCode(0);
    expect($shop->group->commsStats->number_outboxes)->toBe(23);
})->depends('outbox seeded when shop created');

test('outbox seeded when website created', function (Shop $shop) {
    $website = StoreWebsite::make()->action(
        $shop,
        Website::factory()->definition()
    );
    expect($website->group->commsStats->number_outboxes)->toBe(33)
        ->and($website->organisation->commsStats->number_outboxes)->toBe(33)
        ->and($website->shop->commsStats->number_outboxes)->toBe(21);

    return $website;

})->depends('outbox seeded when shop created');


test('seed websites outboxes by command', function (Website $website) {
    $this->artisan('website:seed-outboxes '.$website->slug)->assertExitCode(0);
    $this->artisan('website:seed-outboxes')->assertExitCode(0);
    expect($website->group->commsStats->number_outboxes)->toBe(33);
})->depends('outbox seeded when website created');


test('outbox seeded when fulfilment created', function () {
    $fulfilment = createFulfilment($this->organisation);
    expect($fulfilment->group->commsStats->number_outboxes)->toBe(37)
        ->and($fulfilment->organisation->commsStats->number_outboxes)->toBe(37)
        ->and($fulfilment->shop->commsStats->number_outboxes)->toBe(4);

    return $fulfilment;

});

test('seed fulfilments outboxes by command', function (Fulfilment $fulfilment) {
    $this->artisan('fulfilment:seed-outboxes '.$fulfilment->slug)->assertExitCode(0);
    $this->artisan('fulfilment:seed-outboxes')->assertExitCode(0);
    expect($fulfilment->group->commsStats->number_outboxes)->toBe(37);
})->depends('outbox seeded when fulfilment created');


test('create mailshot', function (Shop $shop) {

    /** @var Outbox $outbox */
    $outbox = $shop->outboxes()->where('type', OutboxCodeEnum::MARKETING)->first();

    $mailshot = StoreMailshot::make()->action($outbox, Mailshot::factory()->definition());
    $this->assertModelExists($mailshot);

    return $mailshot;
})->depends('outbox seeded when shop created');

test('update mailshot', function ($mailshot) {
    $mailshot = UpdateMailshot::make()->action($mailshot, Mailshot::factory()->definition());
    $this->assertModelExists($mailshot);
    return $mailshot;
})->depends('create mailshot');




test('test post room hydrator', function ($shop) {
    $postRoom = $this->group->postRooms()->first();

    $outbox = StoreOutbox::make()->action(
        $postRoom,
        $shop,
        [
            'type'      => OutboxCodeEnum::NEWSLETTER,
            'name'      => 'Test',
            'blueprint' => OutboxBlueprintEnum::EMAIL_TEMPLATE,
        ]
    );

    expect($outbox)->toBeInstanceOf(Outbox::class)
        ->and($outbox->postRoom->stats->number_outboxes)->toBe(9)
        ->and($outbox->postRoom->stats->number_outboxes_type_newsletter)->toBe(4);

    return $outbox;


})->depends('outbox seeded when shop created')->todo();

test('test attach model to outbox', function (Outbox $outbox) {

    AttachModelToOutbox::make()->action(
        $this->customer,
        $outbox,
    );

    $this->customer->refresh();

    $outbox->refresh();

    expect($this->customer->subscribedOutboxes()->count())->toBe(1)
        ->and($this->customer->unsubscribedOutboxes()->count())->toBe(0)
        ->and($outbox->group->commsStats->number_outbox_subscribers)->toBe(1)
        ->and($outbox->organisation->commsStats->number_outbox_subscribers)->toBe(1)
        ->and($outbox->shop->commsStats->number_outbox_subscribers)->toBe(1)
        ->and($outbox->stats->number_subscribers)->toBe(1)
        ->and($outbox->stats->number_unsubscribed)->toBe(0);

    return $outbox;
})->depends('test post room hydrator');

test('test update model to outbox', function (Outbox $outbox) {

    $unsubscribedAt = Date::now()->toDateString();
    UpdateModelToOutbox::make()->action(
        $this->customer,
        $outbox,
        [
            'data' => "{'test': '1'}",
            'unsubscribed_at' => $unsubscribedAt
        ]
    );

    $this->customer->refresh();

    $modelUnsubscribedToOutbox = $this->customer->unsubscribedOutboxes()
    ->where('outbox_id', $outbox->id)
    ->first();

    $outbox->refresh();

    expect($modelUnsubscribedToOutbox)->toBeInstanceOf(ModelSubscribedToOutbox::class)
        ->and($modelUnsubscribedToOutbox->data)->toBe("{'test': '1'}")
        ->and(Carbon::parse($modelUnsubscribedToOutbox->unsubscribed_at)->toDateString())->toBe($unsubscribedAt);


    expect($this->customer->subscribedOutboxes()->count())->toBe(0)
        ->and($this->customer->unsubscribedOutboxes()->count())->toBe(1)
        ->and($outbox->group->commsStats->number_outbox_subscribers)->toBe(0)
        ->and($outbox->organisation->commsStats->number_outbox_subscribers)->toBe(0)
        ->and($outbox->shop->commsStats->number_outbox_subscribers)->toBe(0)
        ->and($outbox->stats->number_subscribers)->toBe(0)
        ->and($outbox->stats->number_unsubscribed)->toBe(1);

    return $outbox;
})->depends('test post room hydrator');

test('test detach model to outbox', function (Outbox $outbox) {

    DetachModelToOutbox::make()->action(
        $this->customer,
        $outbox,
    );

    $this->customer->refresh();
    $outbox->refresh();

    expect($this->customer->subscribedOutboxes()->count())->toBe(0)
        ->and($this->customer->unsubscribedOutboxes()->count())->toBe(0)
        ->and($outbox->group->commsStats->number_outbox_subscribers)->toBe(0)
        ->and($outbox->organisation->commsStats->number_outbox_subscribers)->toBe(0)
        ->and($outbox->shop->commsStats->number_outbox_subscribers)->toBe(0)
        ->and($outbox->stats->number_subscribers)->toBe(0)
        ->and($outbox->stats->number_unsubscribed)->toBe(0);

})->depends('test update model to outbox');