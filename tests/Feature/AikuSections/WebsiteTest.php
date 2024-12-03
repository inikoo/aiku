<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 28 Jan 2024 15:08:30 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

/** @noinspection PhpUnhandledExceptionInspection */

use App\Actions\Web\Banner\StoreBanner;
use App\Actions\Web\Banner\UpdateBanner;
use App\Actions\Web\ExternalLink\StoreExternalLink;
use App\Actions\Web\ModelHasWebBlocks\DeleteModelHasWebBlocks;
use App\Actions\Web\ModelHasWebBlocks\StoreModelHasWebBlock;
use App\Actions\Web\ModelHasWebBlocks\UpdateModelHasWebBlocks;
use App\Actions\Web\Webpage\StoreWebpage;
use App\Actions\Web\Website\LaunchWebsite;
use App\Actions\Web\Website\StoreWebsite;
use App\Actions\Web\Website\UpdateWebsite;
use App\Enums\Helpers\Snapshot\SnapshotStateEnum;
use App\Enums\Web\Banner\BannerTypeEnum;
use App\Enums\Web\Webpage\WebpageStateEnum;
use App\Enums\Web\Webpage\WebpageTypeEnum;
use App\Enums\Web\Website\WebsiteStateEnum;
use App\Enums\Web\Website\WebsiteTypeEnum;
use App\Models\Dropshipping\ModelHasWebBlocks;
use App\Models\Helpers\Snapshot;
use App\Models\Helpers\SnapshotStats;
use App\Models\Web\Banner;
use App\Models\Web\ExternalLink;
use App\Models\Web\WebBlock;
use App\Models\Web\WebBlockType;
use App\Models\Web\Webpage;
use App\Models\Web\WebpageStats;
use App\Models\Web\Website;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
        ->and($website->webStats->number_webpages)->toBe(15);


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
        ->and($snapshot->stats)->toBeInstanceOf(SnapshotStats::class)
        ->and(Arr::get($snapshot->layout, 'web_blocks'))->toBeArray()
        ->and($snapshot->checksum)->toBeString()
        ->and($snapshot->state)->toBe(SnapshotStateEnum::UNPUBLISHED);

    return $webpage;

})->depends('create b2b website');

test('create model has web block', function (Webpage $webpage) {

    /** @var WebBlockType $webBlockType */
    $webBlockType = $webpage->group->webBlockTypes()->where('code', 'text')->first();
    expect($webBlockType)->toBeInstanceOf(WebBlockType::class);

    $modelHasWebBlock = StoreModelHasWebBlock::make()->action(
        $webpage,
        [
            'web_block_type_id' => $webBlockType->id,
        ]
    );

    expect($modelHasWebBlock)->toBeInstanceOf(ModelHasWebBlocks::class)
        ->and($modelHasWebBlock->webBlock)->toBeInstanceOf(WebBlock::class);

    $webpage->refresh();
    expect($webpage->is_dirty)->toBeTrue();

    return $modelHasWebBlock;

})->depends('create webpage');


test('model external link', function () {
    $externalLink = ExternalLink::class;
    expect($externalLink)->toBe(ExternalLink::class);
    return $externalLink;
});

test('store external link', function (ModelHasWebBlocks $modelHasWebBlock) {
    $group = $modelHasWebBlock->group;
    $webpage = $modelHasWebBlock->webpage;
    $webBlock = $modelHasWebBlock->webBlock;
    $externalLink =   StoreExternalLink::make()->action($group, [
        'url' => 'https://www.google.com',
        'webpage_id' => $webpage->id,
        'web_block_id' => $webBlock->id,
    ]);
    expect($externalLink)->toBeInstanceOf(ExternalLink::class)
        ->and($externalLink->group_id)->toBe($group->id)
        ->and($externalLink->number_websites_shown)->toBe(1)
        ->and($externalLink->number_webpages_shown)->toBe(1)
        ->and($externalLink->number_web_blocks_shown)->toBe(1)
        ->and($externalLink->number_websites_hidden)->toBe(0)
        ->and($externalLink->number_webpages_hidden)->toBe(0)
        ->and($externalLink->number_web_blocks_hidden)->toBe(0)
        ->and($externalLink->status)->toBe('200');

    return $externalLink;
})->depends("create model has web block");

test('model external link has web blocks', function (ExternalLink $externalLink) {
    $webBlocks = $externalLink->webBlocks;
    expect($webBlocks)->toBeInstanceOf(Collection::class)
    ->and(count($webBlocks->toArray()))->toBeGreaterThan(0)
    ->and($webBlocks[0])->toBeInstanceOf(WebBlock::class);
})->depends('store external link');

test('model external link has webpages', function (ExternalLink $externalLink) {
    $webpages = $externalLink->webpages;
    expect($webpages)->toBeInstanceOf(Collection::class)
    ->and(count($webpages->toArray()))->toBeGreaterThan(0)
    ->and($webpages[0])->toBeInstanceOf(Webpage::class);
})->depends('store external link');

test('model external link has websites', function (ExternalLink $externalLink) {
    $websites = $externalLink->websites;
    expect($websites)->toBeInstanceOf(Collection::class)
    ->and(count($websites->toArray()))->toBeGreaterThan(0)
    ->and($websites[0])->toBeInstanceOf(Website::class);
})->depends('store external link');

test('update model has web block', function (ModelHasWebBlocks $modelHasWebBlock) {
    $modelHasWebBlock = UpdateModelHasWebBlocks::make()->action($modelHasWebBlock, ['layout' => ['text' => 'Test Text']]);
    expect($modelHasWebBlock)->toBeInstanceOf(ModelHasWebBlocks::class);
})->depends('create model has web block');

test('delete model has web block', function (ModelHasWebBlocks $modelHasWebBlock) {
    // clean up external links
    DB::table('web_block_has_external_link')->where('group_id', $modelHasWebBlock->group_id)->delete();
    DB::table('model_has_web_blocks')->where('group_id', $modelHasWebBlock->group_id)->delete();

    $modelHasWebBlock = DeleteModelHasWebBlocks::make()->action($modelHasWebBlock, []);
    expect($modelHasWebBlock)->toBeInstanceOf(ModelHasWebBlocks::class);
})->depends('create model has web block');

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
        ->and($website->webStats->number_webpages)->toBe(9);

    /** @var Webpage $homeWebpage */
    $homeWebpage = $website->webpages()->first();
    expect($homeWebpage->type)->toBe(WebpageTypeEnum::STOREFRONT)
        ->and($homeWebpage->state)->toBe(WebpageStateEnum::READY)
        ->and($homeWebpage->ready_at)->toBeInstanceOf(Carbon::class)
        ->and($homeWebpage->level)->toBe(1)
        ->and($homeWebpage->stats->number_webpages)->toBe(2)
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

    expect($website->webStats->number_webpages)->toBe(9);
})->depends('launch fulfilment website from command');

test('store hello banner', function (Website $website) {
    $banner = StoreBanner::make()->action($website, [
        'name' => 'hello',
        'type' => BannerTypeEnum::LANDSCAPE,
    ]);

    expect($banner)->toBeInstanceOf(Banner::class)
        ->and($banner->name)->toBe('hello')
        ->and($banner->type)->toBe(BannerTypeEnum::LANDSCAPE);

    return $banner;
})->depends('create b2b website');

test('update hello banner', function (Banner $banner) {
    $banner = UpdateBanner::make()->action($banner, [
        'name' => 'hello2',
    ]);

    expect($banner)->toBeInstanceOf(Banner::class)
        ->and($banner->name)->toBe('hello2')
        ->and($banner->type)->toBe(BannerTypeEnum::LANDSCAPE);

})->depends('store hello banner');
