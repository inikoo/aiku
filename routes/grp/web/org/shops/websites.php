<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Jan 2024 21:22:15 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\CRM\WebUser\IndexWebUsers;
use App\Actions\CRM\WebUser\ShowWebUser;
use App\Actions\Mail\Outbox\UI\IndexOutboxes;
use App\Actions\Mail\Outbox\UI\ShowOutbox;
use App\Actions\Web\Banner\UI\CreateBanner;
use App\Actions\Web\Banner\UI\IndexBanners;
use App\Actions\Web\Banner\UI\ShowBanner;
use App\Actions\Web\Banner\UI\ShowBannerWorkshop;
use App\Actions\Web\Webpage\UI\CreateWebpage;
use App\Actions\Web\Webpage\UI\EditWebpage;
use App\Actions\Web\Webpage\UI\IndexWebpages;
use App\Actions\Web\Webpage\UI\ShowFooter;
use App\Actions\Web\Webpage\UI\ShowHeader;
use App\Actions\Web\Webpage\UI\ShowMenu;
use App\Actions\Web\Webpage\UI\ShowWebpage;
use App\Actions\Web\Webpage\UI\ShowWebpageWorkshop;
use App\Actions\Web\Webpage\UI\ShowWebpageWorkshopPreview;
use App\Actions\Web\Website\UI\CreateWebsite;
use App\Actions\Web\Website\UI\EditWebsite;
use App\Actions\Web\Website\UI\IndexWebsites;
use App\Actions\Web\Website\UI\ShowWebsite;
use App\Actions\Web\Website\UI\ShowWebsiteWorkshop;
use App\Actions\Web\Website\UI\ShowWebsiteWorkshopPreview;
use Illuminate\Support\Facades\Route;

Route::name('websites.')->group(function () {
    Route::get('/', [IndexWebsites::class, 'inShop'])->name('index');
    Route::get('/create', [CreateWebsite::class, 'inShop'])->name('create');

    Route::prefix('{website}')
        ->group(function () {
            Route::get('', ShowWebsite::class)->name('show');
            Route::get('edit', EditWebsite::class)->name('edit');
            Route::get('outboxes', [IndexOutboxes::class, 'inWebsite'])->name('outboxes');
            Route::get('outboxes/{outbox}', [ShowOutbox::class, 'inWebsite'])->name('outboxes.show');

            Route::name('workshop')->prefix('workshop')
                ->group(function () {
                    Route::get('', ShowWebsiteWorkshop::class)->name('');
                    Route::get('preview', ShowWebsiteWorkshopPreview::class)->name('.preview');
                    Route::get('footer', [ShowFooter::class, 'inShop'])->name('.footer');
                    Route::get('header', [ShowHeader::class, 'inShop'])->name('.header');
                    Route::get('menu', [ShowMenu::class, 'inShop'])->name('.menu');
                });
        });
});


Route::prefix('{website}/webpages')->name('webpages.')->group(function () {
    Route::get('', IndexWebpages::class)->name('index');
    Route::get('create', CreateWebpage::class)->name('create');
    Route::get('edit', EditWebpage::class)->name('edit');
    Route::prefix('{webpage}')
        ->group(function () {
            Route::get('', ShowWebpage::class)->name('show');
            Route::get('workshop', ShowWebpageWorkshop::class)->name('workshop');
            Route::get('workshop/preview', ShowWebpageWorkshopPreview::class)->name('preview');
            Route::get('webpages', [IndexWebpages::class, 'inWebpage'])->name('show.webpages.index');
        });
});

Route::prefix('{website}/web-users')->name('web_users.')->group(function () {
    Route::get('', IndexWebUsers::class)->name('index');
    Route::get('{webUser}', ShowWebUser::class)->name('show');
});

Route::prefix('{website}/banners')->name('banners.')->group(function () {
    Route::get('', IndexBanners::class)->name('index');
    Route::get('/create', CreateBanner::class)->name('create');
    Route::get('/{banner}/workshop', ShowBannerWorkshop::class)->name('workshop');
    Route::get('/{banner}', ShowBanner::class)->name('show');
});
