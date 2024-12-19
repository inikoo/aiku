<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 26 Nov 2024 21:08:48 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

/** @noinspection PhpUnhandledExceptionInspection */

namespace Tests\Feature;

use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\Comms\Email\SendResetPasswordEmail;
use App\Actions\Comms\Mailshot\StoreMailshot;
use App\Actions\Comms\Mailshot\UpdateMailshot;
use App\Actions\Comms\Outbox\StoreOutbox;
use App\Actions\Web\Website\StoreWebsite;
use App\Enums\Comms\Outbox\OutboxCodeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Comms\Mailshot;
use App\Models\Comms\Outbox;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Web\Website;
use Config;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;

beforeAll(function () {
    loadDB();
});


beforeEach(
    /**
     * @throws \Throwable
     */
    function () {
        list(
            $this->organisation,
            $this->user,
            $this->shop
        ) = createShop();
        $this->customer = createCustomer($this->shop);
        $this->group    = $this->organisation->group;

        Config::set(
            'inertia.testing.page_paths',
            [resource_path('js/Pages/Grp')]
        );
        actingAs($this->user);
    }
);

test('post rooms seeded correctly', function () {
    $postRooms = $this->group->postRooms;
    expect($postRooms->count())->toBe(7)
        ->and($this->group->commsStats->number_post_rooms)->toBe(7);
});

test('run seed post rooms command', function () {
    $this->artisan('group:seed_post_rooms '.$this->group->slug)->assertExitCode(0);
    expect($this->group->commsStats->number_post_rooms)->toBe(7);
});

test('seed organisation outboxes customers command', function () {
    $this->artisan('org:seed_outboxes '.$this->organisation->slug)->assertExitCode(0);
    $this->artisan('org:seed_outboxes')->assertExitCode(0);
    expect($this->group->commsStats->number_outboxes)->toBe(12)
        ->and($this->organisation->commsStats->number_outboxes)->toBe(12)
        ->and($this->organisation->commsStats->number_outboxes_type_test)->toBe(1)
        ->and($this->organisation->commsStats->number_outboxes_state_active)->toBe(9);
});

test(
    'outbox seeded when shop created',
    function () {
        $shop = StoreShop::make()->action($this->organisation, Shop::factory()->definition());
        expect($shop->group->commsStats->number_outboxes)->toBe(23)
            ->and($shop->organisation->commsStats->number_outboxes)->toBe(23)
            ->and($shop->commsStats->number_outboxes)->toBe(11);

        return $shop;
    }
);

test('seed shop outboxes by command', function (Shop $shop) {
    $this->artisan('shop:seed_outboxes '.$shop->slug)->assertExitCode(0);
    $this->artisan('shop:seed_outboxes')->assertExitCode(0);
    expect($shop->group->commsStats->number_outboxes)->toBe(23);
})->depends('outbox seeded when shop created');

test('outbox seeded when website created', function (Shop $shop) {
    $website = StoreWebsite::make()->action(
        $shop,
        Website::factory()->definition()
    );
    expect($website->group->commsStats->number_outboxes)->toBe(32)
        ->and($website->organisation->commsStats->number_outboxes)->toBe(32)
        ->and($website->shop->commsStats->number_outboxes)->toBe(20);

    return $website;
})->depends('outbox seeded when shop created');


test('seed websites outboxes by command', function (Website $website) {
    $this->artisan('website:seed_outboxes '.$website->slug)->assertExitCode(0);
    $this->artisan('website:seed_outboxes')->assertExitCode(0);
    expect($website->group->commsStats->number_outboxes)->toBe(32);
})->depends('outbox seeded when website created');


test(
    'outbox seeded when fulfilment created',
    function () {
        $fulfilment = createFulfilment($this->organisation);
        expect($fulfilment->group->commsStats->number_outboxes)->toBe(40)
            ->and($fulfilment->organisation->commsStats->number_outboxes)->toBe(40)
            ->and($fulfilment->shop->commsStats->number_outboxes)->toBe(8);

        return $fulfilment;
    }
);

test('seed fulfilments outboxes by command', function (Fulfilment $fulfilment) {
    $this->artisan('fulfilment:seed_outboxes '.$fulfilment->slug)->assertExitCode(0);
    $this->artisan('fulfilment:seed_outboxes')->assertExitCode(0);
    expect($fulfilment->group->commsStats->number_outboxes)->toBe(40);
})->depends('outbox seeded when fulfilment created');


test(
    'create mailshot',
    function (Shop $shop) {
        /** @var Outbox $outbox */
        $outbox = $shop->outboxes()->where('type', OutboxCodeEnum::MARKETING)->first();

        $mailshot = StoreMailshot::make()->action($outbox, Mailshot::factory()->definition());
        $this->assertModelExists($mailshot);

        return $mailshot;
    }
)->depends('outbox seeded when shop created');

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
        ]
    );

    expect($outbox)->toBeInstanceOf(Outbox::class)
        ->and($outbox->postRoom->stats->number_outboxes)->toBe(9)
        ->and($outbox->postRoom->stats->number_outboxes_type_newsletter)->toBe(4);

    return $outbox;
})->depends('outbox seeded when shop created')->todo();


test('test send email reset password', function ($shop) {
    $customer = $this->customer;

    $html = SendResetPasswordEmail::make()->action($customer, []);

    expect($html)->toBeString();

    return $customer;
})->depends('outbox seeded when shop created')->todo();

test('UI index mail outboxes', function () {
    $response = $this->get(route('grp.org.shops.show.comms.outboxes.index', [$this->organisation->slug, $this->shop->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Comms/Outboxes')
            ->has('title')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                    ->where('title', 'outboxes')
                    ->etc()
            )
            ->has('data')
            ->has('breadcrumbs', 4);
    });
});

test('UI show mail outboxes', function () {
    $outbox = $this->shop->outboxes()->first();
    $response = $this->get(route('grp.org.shops.show.comms.outboxes.show', [$this->organisation->slug, $this->shop->slug, $outbox]));

    $response->assertInertia(function (AssertableInertia $page) use ($outbox) {
        $page
            ->component('Comms/Outbox')
            ->has('title')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                    ->where('title', $outbox->name)
                    ->etc()
            )
            ->has('navigation')
            ->has('tabs')
            ->has('breadcrumbs', 5);
    });
});

test('UI Index Org Post Rooms', function () {
    $response = $this->get(route('grp.org.shops.show.comms.post-rooms.index', [$this->organisation->slug, $this->shop->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Comms/OrgPostRooms')
            ->has('title')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                    ->where('title', 'Post Room')
                    ->etc()
            )
            ->has('data')
            ->has('breadcrumbs', 4);
    });
});

test('UI Show Org Post Rooms', function () {
    $orgPostRoom = $this->organisation->orgPostRooms()->first();
    $response = $this->get(route('grp.org.shops.show.comms.post-rooms.show', [$this->organisation->slug, $this->shop->slug, $orgPostRoom->slug]));

    $response->assertInertia(function (AssertableInertia $page) use ($orgPostRoom) {
        $page
            ->component('Comms/PostRoom')
            ->has('title')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                    ->where('title', $orgPostRoom->name)
                    ->etc()
            )
            ->has('navigation')
            ->has('data')
            ->has('breadcrumbs', 5);
    });
});

test('UI Index MArketing Mailshots', function () {
    $response = $this->get(route('grp.org.shops.show.marketing.mailshots.index', [$this->organisation->slug, $this->shop->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Comms/Mailshots')
            ->has('title')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                    ->where('title', 'mailshots')
                    ->etc()
            )
            ->has('data')
            ->has('breadcrumbs', 3);
    });
});

test('UI Index Newsletter Mailshots', function () {
    $response = $this->get(route('grp.org.shops.show.marketing.newsletters.index', [$this->organisation->slug, $this->shop->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Comms/Mailshots')
            ->has('title')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                    ->where('title', 'newsletter')
                    ->etc()
            )
            ->has('data')
            ->has('breadcrumbs', 3);
    });
});
