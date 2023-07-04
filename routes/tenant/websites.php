<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 13 Oct 2022 15:46:44 Central European Summer Time, Plane Malaga - East Midlands UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


use App\Actions\UI\Websites\WebsiteDashboard;
use App\Actions\UI\Websites\WebsitesDashboard;
use App\Actions\Web\WebpageVariant\IndexWebpageVariants;
use App\Actions\Web\Website\UI\CreateWebsite;
use App\Actions\Web\Website\UI\EditWebsite;
use App\Actions\Web\Website\UI\IndexWebsites;
use App\Actions\Web\Website\UI\RemoveWebsite;
use App\Actions\Web\Website\UI\ShowWebsite;
use App\Actions\Web\Website\UI\ShowWebsiteWorkshop;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', WebsitesDashboard::class)->name('dashboard');

Route::get('/', IndexWebsites::class)->name('index');
Route::get('/create', CreateWebsite::class)->name('create');
Route::get('/webpages', IndexWebpageVariants::class)->name('webpages.index');


Route::get('/{website}', ShowWebsite::class)->name('show');
Route::get('/{website}/edit', EditWebsite::class)->name('edit');
Route::get('/{website}/workshop', ShowWebsiteWorkshop::class)->name('workshop');
Route::get('/{website}/delete', RemoveWebsite::class)->name('remove');
Route::get('/{website}/dashboard', WebsiteDashboard::class)->name('show.dashboard');


Route::get('/{website}/webpages', IndexWebpageVariants::class)->name('show.webpages.index');
