<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 02 Dec 2024 15:57:14 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Comms\Outbox\StoreOutbox;
use App\Enums\Comms\Outbox\OutboxBlueprintEnum;
use App\Enums\Comms\Outbox\OutboxCodeEnum;
use App\Models\Comms\Outbox;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;

beforeAll(function () {
    loadDB();
});

beforeEach(function () {

    list(
        $this->organisation,
        $this->user,
        $this->shop
    )                        = createShop();
    $postRoom = $this->shop->group->postRooms()->first();
    $outbox = Outbox::first();
    if (!$outbox) {
        $outbox = StoreOutbox::make()->action(
            $postRoom,
            $this->shop,
            [
                'type'      => OutboxCodeEnum::NEWSLETTER,
                'name'      => 'Test',
                'blueprint' => OutboxBlueprintEnum::EMAIL_TEMPLATE,
                'layout'    => []
            ]
        );
    }
    $this->outbox = $outbox;
    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    actingAs($this->user);
});

test('UI index mail outboxes', function () {
    $response = $this->get(route('grp.org.shops.show.comms.outboxes.index', [$this->organisation->slug, $this->shop->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Mail/Outboxes')
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
    $response = $this->get(route('grp.org.shops.show.comms.outboxes.show', [$this->organisation->slug, $this->shop->slug, $this->outbox]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Mail/Outbox')
            ->has('title')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->outbox->name)
                        ->etc()
            )
            ->has('navigation')
            ->has('tabs')
            ->has('breadcrumbs', 5);
    });
});
