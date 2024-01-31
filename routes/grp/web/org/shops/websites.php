<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Jan 2024 21:22:15 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Web\Webpage\IndexWebpages;
use App\Actions\Web\Website\UI\CreateWebsite;
use App\Actions\Web\Website\UI\EditWebsite;
use App\Actions\Web\Website\UI\IndexWebsites;
use App\Actions\Web\Website\UI\ShowWebsite;
use App\Actions\Web\Website\UI\ShowWebsiteWorkshop;
use Illuminate\Support\Facades\Route;

Route::get('/', [IndexWebsites::class,'inShop'])->name('index');
Route::get('/create', [CreateWebsite::class, 'inShop'])->name('create');
Route::get('/{website}', [ShowWebsite::class,'inShop'])->name('show');

Route::prefix('{website}')
    ->group(function () {
        Route::get('', ShowWebsite::class)->name('show');
        Route::get('edit', EditWebsite::class)->name('edit');
        Route::get('workshop', ShowWebsiteWorkshop::class)->name('workshop');

        Route::name('show.')
            ->group(function () {
                Route::get('/webpages', IndexWebpages::class)->name('webpages.index');
                //   Route::get('/webpages/{webpage}', ShowWebpage::class)->name('webpages.index');
            });


    });
