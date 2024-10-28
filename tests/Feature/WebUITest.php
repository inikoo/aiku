<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 28 Jan 2024 15:08:30 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Web\Banner\StoreBanner;
use App\Actions\Web\Website\LaunchWebsite;
use App\Enums\Web\Banner\BannerTypeEnum;
use App\Enums\Web\Website\WebsiteStateEnum;
use App\Models\Web\Banner;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses()->group('ui');

beforeAll(function () {
    loadDB();
});
beforeEach(function () {
    list(
        $this->organisation,
        $this->user,
        $this->shop
    )                        = createShop();
    $this->warehouse         = createWarehouse();
    $this->fulfilment        = createFulfilment($this->organisation);
    $this->fulfilmentWebsite = createWebsite($this->fulfilment->shop);
    if ($this->fulfilmentWebsite->state == WebsiteStateEnum::IN_PROCESS) {
        LaunchWebsite::make()->action($this->fulfilmentWebsite);
    }

    $banner = Banner::first();

    if (!$banner) {
        $banner = StoreBanner::make()->action($this->fulfilmentWebsite, [
            'name' => 'fulfilmentBanner',
            'type' => BannerTypeEnum::LANDSCAPE,
        ]);
        $banner->refresh();
    }
    $this->banner = $banner;


    $this->user->refresh();
    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    actingAs($this->user);
});



test('can show fulfilment website', function () {

    $website = $this->fulfilmentWebsite;

    $response = get(
        route(
            'grp.org.fulfilments.show.web.websites.show',
            [
                $this->organisation->slug,
                $this->fulfilment->slug,
                $website->slug
            ]
        )
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Web/Website')
            ->has('title')
            ->has('breadcrumbs', 2);
    });
});

test('can show webpages list in fulfilment website', function () {

    $website  = $this->fulfilmentWebsite;
    $response = get(
        route(
            'grp.org.fulfilments.show.web.webpages.index',
            [
                $this->organisation->slug,
                $this->fulfilment->slug,
                $website->slug
            ]
        )
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Web/Webpages')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has('data.data', 9);
    });
});

test('index banner', function () {
    $response = get(
        route(
            'grp.org.shops.show.web.banners.index',
            [
                $this->organisation->slug,
                $this->shop->slug,
                $this->fulfilmentWebsite->slug
            ]
        )
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Web/Banners/Banners')
            ->has('title')
            ->has('breadcrumbs', 3)
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", "banners")->etc()
            )
            ->has('data');
    });
});
