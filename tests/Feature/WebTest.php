<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 28 Jan 2024 15:08:30 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Web\Webpage\StoreWebpage;
use App\Actions\Web\Website\LaunchWebsite;
use App\Actions\Web\Website\StoreWebsite;
use App\Actions\Web\Website\UpdateWebsite;
use App\Enums\Helpers\Snapshot\SnapshotStateEnum;
use App\Enums\Web\Webpage\WebpageStateEnum;
use App\Enums\Web\Webpage\WebpageTypeEnum;
use App\Enums\Web\Website\WebsiteStateEnum;
use App\Enums\Web\Website\WebsiteTypeEnum;
use App\Models\Helpers\Snapshot;
use App\Models\Web\Webpage;
use App\Models\Web\WebpageStats;
use App\Models\Web\Website;

use Illuminate\Support\Carbon;

use function Pest\Laravel\actingAs;

beforeAll(function () {
    loadDB();
});
beforeEach(function () {
    list(
        $this->organisation,
        $this->user,
        $this->shop
    )                 = createShop();
    $this->warehouse  = createWarehouse();
    $this->fulfilment = createFulfilment($this->organisation);

    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    actingAs($this->user);
});

test('create b2b website', function () {
    $website = StoreWebsite::make()->action(
        $this->shop,
        Website::factory()->definition()
    );

    expect($website)->toBeInstanceOf(Website::class)
        ->and($website->storefront)->toBeInstanceOf(Webpage::class)
        ->and($website->webStats->number_webpages)->toBe(4);


    return $website;
});

test('launch website', function (Website $website) {
    $website = LaunchWebsite::make()->action($website);
    $website->refresh();

    expect($website->state)->toBe(WebsiteStateEnum::LIVE)
        ->and($website->status)->toBeTrue()
        ->and($website->launched_at)->toBeInstanceOf(Carbon::class);


    $home = $website->storefront;
    expect($home)->toBeInstanceOf(Webpage::class)
        ->and($home->state)->toBe(WebpageStateEnum::LIVE)
        ->and($home->live_at)->toBeInstanceOf(Carbon::class)
        ->and($home->stats->number_snapshots)->toBe(2)
        ->and($home->stats->number_deployments)->toBe(1);
})->depends('create b2b website');


test('update website', function (Website $website) {
    $updateData = [
        'name' => 'Test Website Updated',
    ];

    $shop = UpdateWebsite::make()->action($website, $updateData);
    $shop->refresh();

    expect($shop->name)->toBe('Test Website Updated');
})->depends('create b2b website');

test('create webpage', function (Website $website) {
    $webpage = StoreWebpage::make()->action($website->storefront, Webpage::factory()->definition());

    expect($webpage)->toBeInstanceOf(Webpage::class)
        ->and($webpage->level)->toBe(2)
        ->and($webpage->state)->toBe(WebpageStateEnum::IN_PROCESS)
        ->and($webpage->is_fixed)->toBeFalse()
        ->and($webpage->stats)->toBeInstanceOf(WebpageStats::class)
        ->and($webpage->unpublishedSnapshot)->toBeInstanceOf(Snapshot::class);

    $snapshot = $webpage->unpublishedSnapshot;

    expect($snapshot->layout)->toBeArray()
        ->and(Arr::get($snapshot->layout, 'blocks'))->toBeArray()
        ->and($snapshot->checksum)->toBeString()
        ->and($snapshot->state)->toBe(SnapshotStateEnum::UNPUBLISHED);
})->depends('create b2b website');


// Fulfilment Website

test('create fulfilment website', function () {
    $website = StoreWebsite::make()->action(
        $this->fulfilment->shop,
        Website::factory()->definition()
    );


    expect($website)->toBeInstanceOf(Website::class)
        ->and($website->type)->toBe(WebsiteTypeEnum::FULFILMENT)
        ->and($website->state)->toBe(WebsiteStateEnum::IN_PROCESS)
        ->and($website->storefront)->toBeInstanceOf(Webpage::class)
        ->and($website->webStats->number_webpages)->toBe(4);

    /** @var Webpage $homeWebpage */
    $homeWebpage = $website->webpages()->first();
    expect($homeWebpage->type)->toBe(WebpageTypeEnum::STOREFRONT)
        ->and($homeWebpage->state)->toBe(WebpageStateEnum::READY)
        ->and($homeWebpage->ready_at)->toBeInstanceOf(Carbon::class)
        ->and($homeWebpage->level)->toBe(1)
        ->and($homeWebpage->stats->number_webpages)->toBe(3)
        ->and($homeWebpage->stats->number_snapshots)->toBe(1)
        ->and($homeWebpage->stats->number_deployments)->toBe(0)
        ->and($homeWebpage->unpublishedSnapshot)->toBeInstanceOf(Snapshot::class)
        ->and($homeWebpage->unpublishedSnapshot->layout)->toBeArray();

    return $website;
});

test('launch fulfilment website from command', function (Website $website) {
    $this->artisan('website:launch', ['website' => $website->slug])
        ->expectsOutput('Website launched 🚀')
        ->assertExitCode(0);
    $website->refresh();

    expect($website->state)->toBe(WebsiteStateEnum::LIVE);

    /** @var Webpage $homeWebpage */
    $homeWebpage = $website->webpages()->first();
    expect($homeWebpage->type)->toBe(WebpageTypeEnum::STOREFRONT)
        ->and($homeWebpage->ready_at)->toBeInstanceOf(Carbon::class)
        ->and($homeWebpage->live_at)->toBeInstanceOf(Carbon::class)
        ->and($homeWebpage->stats->number_snapshots)->toBe(2)
        ->and($homeWebpage->stats->number_deployments)->toBe(1);

    return $website;
})->depends('create fulfilment website');


// Hydrator commands

test('hydrate website from command', function (Website $website) {
    $this->artisan('website:hydrate', [
        'organisations' => $this->organisation->slug,
        '--slugs'       => $website->slug
    ])
        ->assertExitCode(0);
    $website->refresh();

    expect($website->webStats->number_webpages)->toBe(4);
})->depends('launch fulfilment website from command');
