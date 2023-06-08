<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 13 Oct 2022 15:46:44 Central European Summer Time, Plane Malaga - East Midlands UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


use App\Actions\UI\Websites\WebsiteDashboard;
use App\Actions\UI\Websites\WebsitesDashboard;
use App\Actions\Web\WebpageVariant\IndexWebpageVariants;
use App\Actions\Web\Website\UI\IndexWebsites;
use App\Actions\Web\Website\UI\ShowWebsite;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', WebsitesDashboard::class)->name('dashboard');

Route::get('/', IndexWebsites::class)->name('index');
Route::get('/{website}', ShowWebsite::class)->name('show');
Route::get('/{website}/dashboard', WebsiteDashboard::class)->name('show.dashboard');

Route::get('/{website}/webpage', IndexWebpageVariants::class)->name('show.webpages.index');
