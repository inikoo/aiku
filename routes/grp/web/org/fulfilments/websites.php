<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Jan 2024 18:56:15 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\CRM\WebUser\IndexWebUsers;
use App\Actions\CRM\WebUser\ShowWebUser;
use App\Actions\Web\Webpage\UI\CreateArticle;
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
    Route::get('/', [IndexWebsites::class, 'inFulfilment'])->name('index');
    Route::get('/create', [CreateWebsite::class, 'inFulfilment'])->name('create');

    Route::prefix('{website}')
        ->group(function () {
            Route::get('', [ShowWebsite::class, 'inFulfilment'])->name('show');
            Route::get('edit', [EditWebsite::class, 'inFulfilment'])->name('edit');

            Route::name('workshop')->prefix('workshop')
                ->group(function () {
                    Route::get('', [ShowWebsiteWorkshop::class, 'inFulfilment'])->name('');
                    Route::get('preview', [ShowWebsiteWorkshopPreview::class,'inFulfilment'])->name('.preview');
                    Route::get('footer', [ShowFooter::class,'inFulfilment'])->name('.footer');
                    Route::get('header', [ShowHeader::class,'inFulfilment'])->name('.header');
                    Route::get('menu', [ShowMenu::class, 'inFulfilment'])->name('.menu');
                });

        });
});

Route::prefix('{website}/webpages')->name('webpages.')->group(function () {
    Route::get('', [IndexWebpages::class, 'inFulfilment'])->name('index');
    Route::get('create', [CreateWebpage::class,'inFulfilment'])->name('create');
    Route::get('edit', [EditWebpage::class, 'inFulfilment'])->name('edit');

    Route::prefix('{webpage}')
        ->group(function () {
            Route::get('', [ShowWebpage::class, 'inFulfilment'])->name('show');
            Route::get('workshop', [ShowWebpageWorkshop::class, 'inFulfilment'])->name('workshop');
            Route::get('workshop/preview', [ShowWebpageWorkshopPreview::class, 'inFulfilment'])->name('preview');

            Route::get('webpages', [IndexWebpages::class, 'inWebpageInFulfilment'])->name('show.webpages.index');
        });
});

Route::prefix('{website}/web-users')->name('web_users.')->group(function () {
    Route::get('', [IndexWebUsers::class,'inFulfilment'])->name('index');
    Route::get('{webUser}', [ShowWebUser::class,'inFulfilment'])->name('show');
});

//  Route::get('/blog/article/create', CreateArticle::class)->name('show.blog.article.create');
