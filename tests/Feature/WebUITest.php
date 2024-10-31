<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 28 Jan 2024 15:08:30 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Web\Banner\StoreBanner;
use App\Actions\Web\Website\LaunchWebsite;
use App\Enums\UI\Web\WebsiteTabsEnum;
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

test('can show fulfilment website (tab showcase)', function () {

    $website = $this->fulfilmentWebsite;

    $response = get(
        route(
            'grp.org.fulfilments.show.web.websites.show',
            [
                $this->organisation->slug,
                $this->fulfilment->slug,
                $website->slug,
                'tab' => WebsiteTabsEnum::SHOWCASE->value
            ]
        )
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Web/Website')
            ->has('title')
            ->has(
                "tabs",
                fn (AssertableInertia $page) => $page->where("current", WebsiteTabsEnum::SHOWCASE->value)->etc()
            )
            ->has(WebsiteTabsEnum::SHOWCASE->value)
            ->has('breadcrumbs', 2);
    });
});

test('can show fulfilment website (tab external links)', function () {

    $website = $this->fulfilmentWebsite;

    $response = get(
        route(
            'grp.org.fulfilments.show.web.websites.show',
            [
                $this->organisation->slug,
                $this->fulfilment->slug,
                $website->slug,
                'tab' => WebsiteTabsEnum::EXTERNAL_LINKS->value
            ]
        )
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Web/Website')
            ->has('title')
            ->has(
                "tabs",
                fn (AssertableInertia $page) => $page->where("current", WebsiteTabsEnum::EXTERNAL_LINKS->value)->etc()
            )
            ->has(WebsiteTabsEnum::EXTERNAL_LINKS->value)
            ->has('breadcrumbs', 2);
    });
});

test('can show fulfilment website (tab analytics)', function () {

    $website = $this->fulfilmentWebsite;

    $response = get(
        route(
            'grp.org.fulfilments.show.web.websites.show',
            [
                $this->organisation->slug,
                $this->fulfilment->slug,
                $website->slug,
                'tab' => WebsiteTabsEnum::ANALYTICS->value
            ]
        )
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Web/Website')
            ->has('title')
            ->has(
                "tabs",
                fn (AssertableInertia $page) => $page->where("current", WebsiteTabsEnum::ANALYTICS->value)->etc()
            )
            ->has(WebsiteTabsEnum::ANALYTICS->value)
            ->has('breadcrumbs', 2);
    });
})->todo();

test('can show fulfilment website (tab web users)', function () {

    $website = $this->fulfilmentWebsite;

    $response = get(
        route(
            'grp.org.fulfilments.show.web.websites.show',
            [
                $this->organisation->slug,
                $this->fulfilment->slug,
                $website->slug,
                'tab' => WebsiteTabsEnum::WEB_USERS->value
            ]
        )
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Web/Website')
            ->has('title')
            ->has(
                "tabs",
                fn (AssertableInertia $page) => $page->where("current", WebsiteTabsEnum::WEB_USERS->value)->etc()
            )
            ->has(WebsiteTabsEnum::WEB_USERS->value)
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

test('can show fulfilments website workshop', function () {

    $website = $this->fulfilmentWebsite;

    $response = get(
        route(
            'grp.org.fulfilments.show.web.websites.workshop',
            [
                $this->organisation->slug,
                $this->fulfilment->slug,
                $website->slug
            ]
        )
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Web/Workshop/WebsiteWorkshop')
            ->where('title', "Website's workshop")
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", "Workshop")->etc()
            )
            ->has('breadcrumbs', 3)
            ->has('tabs');
    });
});

test('can show fulfilments website workshop (header)', function () {

    $website = $this->fulfilmentWebsite;

    $response = get(
        route(
            'grp.org.fulfilments.show.web.websites.workshop.header',
            [
                $this->organisation->slug,
                $this->fulfilment->slug,
                $website->slug
            ]
        )
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Web/Workshop/Header/HeaderWorkshop')
            ->where('title', "Website Header's Workshop")
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", "Header's Workshop")->etc()
            )
            ->has('breadcrumbs', 0)
            ->has('uploadImageRoute')
            ->has('autosaveRoute')
            ->has('route_list')
            ->has('data')
            ->has('web_block_types');
    });
});

test('can show fulfilments website workshop (footer)', function () {

    $website = $this->fulfilmentWebsite;

    $response = get(
        route(
            'grp.org.fulfilments.show.web.websites.workshop.footer',
            [
                $this->organisation->slug,
                $this->fulfilment->slug,
                $website->slug
            ]
        )
    );
    $response->assertInertia(function (AssertableInertia $page) use ($website) {
        $page
            ->component('Org/Web/Workshop/Footer/FooterWorkshop')
            ->where('title', "footer")
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", $website->code)->etc()
            )
            ->has('breadcrumbs', 0)
            ->has('uploadImageRoute')
            ->has('autosaveRoute')
            ->has('data')
            ->has('webBlockTypes');
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

test('show banner', function () {
    $response = get(
        route(
            'grp.org.shops.show.web.banners.show',
            [
                $this->organisation->slug,
                $this->shop->slug,
                $this->fulfilmentWebsite->slug,
                $this->banner->slug
            ]
        )
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Web/Banners/Banner')
            ->has('title')
            ->has('navigation')
            ->has('breadcrumbs', 0)
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", $this->banner->name)->etc()
            )
            ->has('tabs');
    });
});

test('create banner', function () {
    $response = get(
        route(
            'grp.org.shops.show.web.banners.create',
            [
                $this->organisation->slug,
                $this->shop->slug,
                $this->fulfilmentWebsite->slug,
            ]
        )
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CreateModel')
            ->where('title', 'new banner')
            ->has('breadcrumbs', 4)
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", 'banner')->etc()
            )
            ->has('formData');
    });
});

test('web website workshop menu', function () {
    $response = get(
        route(
            'grp.org.shops.show.web.websites.workshop.menu',
            [
                $this->organisation->slug,
                $this->shop->slug,
                $this->fulfilmentWebsite->slug,
            ]
        )
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Web/Workshop/Menu/MenuWorkshop')
            ->where('title', "Website Menu's Workshop")
            ->has('breadcrumbs')
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", "Menu's Workshop")->etc()
            )
            ->has('autosaveRoute')
            ->has('data')
            ->has('webBlockTypes')
            ->has('uploadImageRoute');
    });
});
